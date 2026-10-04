<?php

namespace App\Models;

use App\Models\Concerns\Auditavel;
use App\Support\Perfis;
use App\Support\PermissoesPerfil;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Perfil de acesso (tabela "roles" do spatie/laravel-permission).
 * O "name" é o código interno: é gerado a partir do nome na criação e não muda mais,
 * porque é ele que liga o perfil aos acessos (tabela acessos_perfil) e aos usuários.
 */
class Perfil extends Role
{
    use Auditavel;

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            've_sigilosos' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Perfil $perfil): void {
            if (blank($perfil->name)) {
                $perfil->name = self::codigoLivre((string) $perfil->rotulo);
            }
        });

        static::saved(fn () => Perfis::limparCache());

        static::updated(function (Perfil $perfil): void {
            // Perfil inativado: quem só tinha ele perde o acesso na hora.
            if ($perfil->wasChanged('ativo') && ! $perfil->ativo) {
                $perfil->users()->get()->each(fn (User $usuario) => $usuario->encerrarAcessos());
            }
        });

        static::deleted(function (Perfil $perfil): void {
            Acesso::where('perfil', $perfil->name)->delete(); // em lote: sem um registro de auditoria por linha
            PermissoesPerfil::limparCache();
            Perfis::limparCache();
        });
    }

    /** O spatie exige o código já na criação: sem ele, é gerado a partir do nome. */
    public static function create(array $attributes = [])
    {
        if (blank($attributes['name'] ?? null)) {
            $attributes['name'] = self::codigoLivre((string) ($attributes['rotulo'] ?? ''));
        }

        return parent::create($attributes);
    }

    /** Descrição usada na auditoria. */
    public function getNomeAttribute(): string
    {
        return $this->rotulo ?: (Perfis::ROTULOS[$this->name] ?? (string) $this->name);
    }

    public function original(): bool
    {
        return Perfis::original((string) $this->name);
    }

    /** Código interno a partir do nome ("Assistência Social" => assistencia_social), sem repetir um existente. */
    private static function codigoLivre(string $rotulo): string
    {
        $base = Str::limit(Str::slug($rotulo, '_'), 40, '') ?: 'perfil';
        $codigo = $base;

        for ($i = 2; self::where('name', $codigo)->exists(); $i++) {
            $codigo = $base.'_'.$i;
        }

        return $codigo;
    }
}
