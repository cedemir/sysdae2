<?php

namespace App\Policies;

use App\Support\Perfis;
use Illuminate\Database\Eloquent\Model;

class RegimePolicy extends BasePolicy
{
    protected array $editores = [Perfis::RESIDENCIA];

    protected array $leitores = [];

    protected function motivoBloqueioExclusao(Model $registro): ?string
    {
        $total = $registro->residencias()->count();

        return $total > 0
            ? "Este regime está atribuído a {$total} residente(s). Inative o regime em vez de excluir."
            : null;
    }
}