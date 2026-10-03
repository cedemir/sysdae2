<?php

namespace App\Policies;

use App\Support\Perfis;
use Illuminate\Database\Eloquent\Model;

class SeriePolicy extends BasePolicy
{
    protected array $editores = [Perfis::RESIDENCIA];

    protected array $leitores = [];

    protected function motivoBloqueioExclusao(Model $registro): ?string
    {
        $total = $registro->turmas()->count();

        return $total > 0
            ? "Esta série é usada por {$total} turma(s). Inative a série em vez de excluir."
            : null;
    }
}