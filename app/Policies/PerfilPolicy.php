<?php

namespace App\Policies;

use App\Models\Perfil;
use Illuminate\Database\Eloquent\Model;

/** Só o administrador gerencia perfis. Os perfis originais e os que têm usuários não podem ser excluídos. */
class PerfilPolicy extends BasePolicy
{
    protected array $editores = [];

    protected array $leitores = [];

    /** @param Perfil $registro */
    protected function motivoBloqueioExclusao(Model $registro): ?string
    {
        if ($registro->original()) {
            return 'Os perfis originais do sistema não podem ser excluídos. Se não for usar, inative.';
        }

        $usuarios = $registro->users()->count();
        if ($usuarios > 0) {
            return "{$usuarios} usuário(s) têm este perfil. Troque o perfil deles antes de excluir.";
        }

        return null;
    }
}
