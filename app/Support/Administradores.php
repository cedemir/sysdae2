<?php

namespace App\Support;

use App\Models\User;

final class Administradores
{
    /** Quantos administradores ATIVOS existem, sem contar o usuário informado. */
    public static function outrosAtivos(?int $ignorarId = null): int
    {
        return User::role(Perfis::ADMIN)
            ->where('ativo', true)
            ->when($ignorarId, fn ($consulta, $id) => $consulta->whereKeyNot($id))
            ->count();
    }

    private function __construct()
    {
    }
}