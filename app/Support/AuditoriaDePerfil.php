<?php

namespace App\Support;

use App\Models\Auditoria;
use App\Models\User;

/** Registra na auditoria a mudança de perfil de um usuário (perfil antigo → perfil novo). */
final class AuditoriaDePerfil
{
    /**
     * @param  list<string>  $antes  nomes dos perfis antes da mudança
     * @param  list<string>  $depois  nomes dos perfis depois da mudança
     */
    public static function registrar(User $usuario, array $antes, array $depois): void
    {
        sort($antes);
        sort($depois);

        if ($antes === $depois) {
            return; // nada mudou
        }

        Auditoria::registrar(
            'alterado',
            'Usuário',
            (int) $usuario->id,
            $usuario->email,
            ['detalhes' => ['perfil' => self::texto($antes).' → '.self::texto($depois)]],
        );
    }

    /** @param list<string> $perfis */
    private static function texto(array $perfis): string
    {
        if ($perfis === []) {
            return '(nenhum)';
        }

        return implode(', ', array_map(fn (string $perfil) => Perfis::rotulo($perfil), $perfis));
    }

    private function __construct() {}
}
