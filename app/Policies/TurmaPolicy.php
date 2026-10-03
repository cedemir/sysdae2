<?php

namespace App\Policies;

use App\Support\Perfis;
use Illuminate\Database\Eloquent\Model;

class TurmaPolicy extends BasePolicy
{
    protected array $editores = [Perfis::RESIDENCIA];

    protected array $leitores = [];

    protected function motivoBloqueioExclusao(Model $registro): ?string
    {
        $total = $registro->matriculas()->count();

        return $total > 0
            ? "Esta turma possui {$total} matrícula(s). Inative a turma em vez de excluir."
            : null;
    }
}