<?php

namespace App\Models\Concerns;

use App\Support\Perfis;
use Illuminate\Database\Eloquent\Builder;

/**
 * Esconde os registros sigilosos de quem não tem perfil que os veja (administrador, psicossocial
 * ou perfil cadastrado com "vê registros sigilosos").
 * Vale para qualquer consulta ao model (telas, relatórios e relacionamentos).
 * Sem usuário autenticado (por exemplo, no console), os sigilosos também ficam ocultos.
 */
trait RestringeSigilo
{
    protected static function bootRestringeSigilo(): void
    {
        static::addGlobalScope('sigilo', function (Builder $consulta): void {
            $usuario = auth()->user();

            if (! Perfis::veSigilosos($usuario)) {
                $consulta->where($consulta->getModel()->getTable().'.sigiloso', false);
            }
        });
    }
}
