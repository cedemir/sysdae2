<?php

namespace App\Support;

use App\Models\Perfil;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Perfis de acesso do sistema.
 *
 * Os cinco perfis abaixo são os originais (mesmos da versão desktop) e não podem ser excluídos.
 * Outros perfis são criados pelo administrador na tela "Perfis" (tabela "roles").
 */
final class Perfis
{
    public const ADMIN = 'admin';

    public const DAE_CENTRAL = 'dae_central';

    public const RESIDENCIA = 'residencia_estudantil';

    public const PSICOSSOCIAL = 'psicossocial';

    public const SOMENTE_CONSULTA = 'somente_consulta';

    /** Perfis originais do sistema. */
    public const TODOS = [
        self::ADMIN,
        self::DAE_CENTRAL,
        self::RESIDENCIA,
        self::PSICOSSOCIAL,
        self::SOMENTE_CONSULTA,
    ];

    /** Nome de exibição dos perfis originais (vale quando o cadastro não tem nome próprio). */
    public const ROTULOS = [
        self::ADMIN => 'Administrador',
        self::DAE_CENTRAL => 'DAE Central',
        self::RESIDENCIA => 'Residência Estudantil',
        self::PSICOSSOCIAL => 'Psicossocial',
        self::SOMENTE_CONSULTA => 'Somente consulta',
    ];

    /** Perfis que sempre veem registros sigilosos, independentemente do cadastro. */
    public const VEEM_SIGILOSOS = [self::ADMIN, self::PSICOSSOCIAL];

    private const CHAVE = 'sysdae.perfis';

    public static function original(string $perfil): bool
    {
        return in_array($perfil, self::TODOS, true);
    }

    public static function rotulo(?string $perfil): string
    {
        return self::rotulos()[$perfil] ?? self::ROTULOS[$perfil] ?? (string) $perfil;
    }

    /**
     * Perfis conhecidos (originais e cadastrados): os originais primeiro, depois os demais por nome.
     *
     * @return array<string, string> perfil => nome de exibição
     */
    public static function rotulos(): array
    {
        if (! app()->bound(self::CHAVE)) {
            $cadastrados = Perfil::query()->orderBy('rotulo')->orderBy('name')->get(['name', 'rotulo'])
                ->mapWithKeys(fn (Perfil $perfil) => [$perfil->name => $perfil->rotulo ?: (self::ROTULOS[$perfil->name] ?? $perfil->name)])
                ->all();

            $originais = [];
            foreach (self::ROTULOS as $nome => $rotulo) {
                $originais[$nome] = $cadastrados[$nome] ?? $rotulo;
            }

            app()->instance(self::CHAVE, $originais + $cadastrados);
        }

        return app(self::CHAVE);
    }

    /** O usuário vê ocorrências e atendimentos sigilosos? */
    public static function veSigilosos(?User $usuario): bool
    {
        if (! $usuario) {
            return false;
        }

        return $usuario->roles->contains(fn (Role $perfil) => ($perfil->ativo ?? true) && self::perfilVeSigilosos($perfil));
    }

    /** O perfil vê registros sigilosos (sempre, se for administrador ou psicossocial)? */
    public static function perfilVeSigilosos(Role $perfil): bool
    {
        return in_array($perfil->name, self::VEEM_SIGILOSOS, true) || (bool) $perfil->ve_sigilosos;
    }

    public static function limparCache(): void
    {
        if (app()->bound(self::CHAVE)) {
            app()->forgetInstance(self::CHAVE);
        }
    }

    private function __construct() {}
}
