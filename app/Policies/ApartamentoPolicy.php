<?php

namespace App\Policies;

use App\Support\Perfis;
use Illuminate\Database\Eloquent\Model;

class ApartamentoPolicy extends BasePolicy
{
    protected array $editores = [Perfis::RESIDENCIA];

    protected array $leitores = [];

    protected function motivoBloqueioExclusao(Model $registro): ?string
    {
        $moradores = $registro->residencias()->count();
        if ($moradores > 0) {
            return "Este apartamento possui {$moradores} morador(es). Transfira-os antes de excluir.";
        }

        $trocas = \App\Models\TrocaApartamento::where('origem_apartamento_id', $registro->getKey())
            ->orWhere('destino_apartamento_id', $registro->getKey())
            ->count();

        return $trocas > 0
            ? "Este apartamento consta no histórico de trocas ({$trocas}). Inative o apartamento em vez de excluir."
            : null;
    }
}