<?php

namespace App\Policies;

use App\Support\Perfis;
use Illuminate\Database\Eloquent\Model;

class AlojamentoPolicy extends BasePolicy
{
    protected array $editores = [Perfis::RESIDENCIA];

    protected array $leitores = [];

    protected function motivoBloqueioExclusao(Model $registro): ?string
    {
        $total = $registro->apartamentos()->count();

        return $total > 0
            ? "Este alojamento possui {$total} apartamento(s). Inative o alojamento em vez de excluir."
            : null;
    }
}