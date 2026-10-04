<?php

namespace App\Models;

use App\Models\Concerns\Auditavel;
use App\Support\Perfis;
use App\Support\PermissoesPerfil;
use App\Support\Recursos;
use Illuminate\Database\Eloquent\Model;

/** Acesso de um perfil a um cadastro: sem acesso, só consulta ou consulta e edição. */
class Acesso extends Model
{
    use Auditavel;

    protected $table = 'acessos_perfil';

    protected $fillable = ['perfil', 'recurso', 'nivel', 'ordem'];

    protected static function booted(): void
    {
        // Qualquer mudança vale na hora para todos os usuários.
        static::saved(fn () => PermissoesPerfil::limparCache());
        static::deleted(fn () => PermissoesPerfil::limparCache());
    }

    /** Descrição usada na auditoria. */
    public function getNomeAttribute(): string
    {
        return Perfis::rotulo($this->perfil).' / '.Recursos::rotulo($this->recurso);
    }

    /** @return array<string, string> níveis que este cadastro aceita */
    public function niveisPermitidos(): array
    {
        return Recursos::niveisPermitidos($this->recurso);
    }
}
