<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Administradores;
use App\Support\Perfis;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

/** Só o administrador gerencia usuários. */
class UserPolicy extends BasePolicy
{
    protected array $editores = [];

    protected array $leitores = [];

    public function delete(User $user, Model $registro): bool|Response
    {
        if (! $user->hasRole(Perfis::ADMIN)) {
            return false;
        }

        if ($registro->is($user)) {
            return Response::deny('Você não pode excluir o seu próprio usuário.');
        }

        if ($registro->hasRole(Perfis::ADMIN) && $registro->ativo && Administradores::outrosAtivos($registro->id) === 0) {
            return Response::deny('Deve existir pelo menos um administrador ativo.');
        }

        return true;
    }
}