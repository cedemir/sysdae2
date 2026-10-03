<?php

namespace App\Policies;

use App\Support\Perfis;
use Illuminate\Database\Eloquent\Model;

class CursoPolicy extends BasePolicy
{
    protected array $editores = [Perfis::RESIDENCIA];

    protected array $leitores = [];

    protected function motivoBloqueioExclusao(Model $registro): ?string
    {
        $total = $registro->turmas()->count();

        return $total > 0
            ? "Este curso possui {$total} turma(s). Inative o curso em vez de excluir."
            : null;
    }
}