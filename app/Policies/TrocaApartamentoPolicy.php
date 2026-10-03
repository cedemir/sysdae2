<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Perfis;
use Illuminate\Database\Eloquent\Model;

class TrocaApartamentoPolicy extends BasePolicy
{
    protected array $editores = [];

    protected array $leitores = [Perfis::RESIDENCIA, Perfis::DAE_CENTRAL];

    public function before(User $user, string $ability): ?bool
    {
        // O admin pode consultar; as demais operações caem nos métodos abaixo (todos negam).
        return in_array($ability, ['viewAny', 'view'], true) && $user->hasRole(Perfis::ADMIN) ? true : null;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $registro): bool
    {
        return false;
    }

    public function delete(User $user, Model $registro): bool
    {
        return false;
    }
}