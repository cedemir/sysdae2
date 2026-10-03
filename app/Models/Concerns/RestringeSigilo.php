<?php

namespace App\Models\Concerns;

use App\Support\Perfis;
use Illuminate\Database\Eloquent\Builder;

/**
 * Esconde os registros sigilosos de quem não é administrador nem da equipe psicossocial.
 * Vale para qualquer consulta ao model (telas, relatórios e relacionamentos).
 * Sem usuário autenticado (por exemplo, no console), os sigilosos também ficam ocultos.
 */
trait RestringeSigilo
{
    protected static function bootRestringeSigilo(): void
    {
        static::addGlobalScope('sigilo', function (Builder $consulta): void {
            $usuario = auth()->user();

            if (! $usuario || ! $usuario->hasAnyRole([Perfis::ADMIN, Perfis::PSICOSSOCIAL])) {
                $consulta->where($consulta->getModel()->getTable() . '.sigiloso', false);
            }
        });
    }
}