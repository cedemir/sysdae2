<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Perfis;
use Illuminate\Database\Eloquent\Model;

/** Só o administrador vê e altera os acessos. As linhas são geradas pelo sistema: ninguém cria nem exclui. */
class AcessoPolicy extends BasePolicy
{
    protected array $editores = [];

    protected array $leitores = [];

    public function before(User $user, string $ability): ?bool
    {
        return in_array($ability, ['viewAny', 'view', 'update'], true) && $user->hasRole(Perfis::ADMIN) ? true : null;
    }

    public function delete(User $user, Model $registro): bool
    {
        return false;
    }
}