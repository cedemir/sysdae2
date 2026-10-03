<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Perfis;
use Illuminate\Database\Eloquent\Model;

class AuditoriaPolicy extends BasePolicy
{
    protected array $editores = [];

    protected array $leitores = [];

    public function before(User $user, string $ability): ?bool
    {
        return in_array($ability, ['viewAny', 'view'], true) && $user->hasRole(Perfis::ADMIN) ? true : null;
    }

    public function delete(User $user, Model $registro): bool
    {
        return false;
    }
}