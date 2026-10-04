<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Perfis;
use App\Support\PermissoesPerfil;
use App\Support\Recursos;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Regras de acesso por perfil.
 *
 * O que cada perfil pode fazer vem da tabela "acessos_perfil" (tela "Acessos por perfil").
 * As listas $editores e $leitores abaixo são só o PADRÃO inicial de cada cadastro: valem
 * enquanto a tabela não tiver uma linha para o perfil.
 *
 * O administrador tem acesso total, mas a exclusão passa sempre pela verificação de vínculos
 * (motivoBloqueioExclusao), inclusive para ele.
 */
abstract class BasePolicy
{
    /** @var list<string> */
    protected array $editores = [];

    /** @var list<string> */
    protected array $leitores = [];

    public function before(User $user, string $ability): ?bool
    {
        if ($ability === 'delete') {
            return null;
        }

        return $user->hasRole(Perfis::ADMIN) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->temNivel($user, [PermissoesPerfil::CONSULTA, PermissoesPerfil::EDICAO]);
    }

    public function view(User $user, Model $registro): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->podeEditar($user);
    }

    public function update(User $user, Model $registro): bool
    {
        return $this->podeEditar($user);
    }

    public function delete(User $user, Model $registro): bool|Response
    {
        if (! $user->hasRole(Perfis::ADMIN) && ! $this->podeEditar($user)) {
            return false;
        }

        $motivo = $this->motivoBloqueioExclusao($registro);

        return $motivo === null ? true : Response::deny($motivo);
    }

    public function deleteAny(User $user): bool
    {
        return $this->podeEditar($user);
    }

    public function restore(User $user, Model $registro): bool
    {
        return $this->podeEditar($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->podeEditar($user);
    }

    public function forceDelete(User $user, Model $registro): bool
    {
        return $this->podeEditar($user);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->podeEditar($user);
    }

    /** Nível inicial do perfil neste cadastro (usado para preencher a tabela de acessos). */
    public function nivelPadrao(string $perfil): string
    {
        $extra = Recursos::padraoDoPerfil($perfil, $this->recurso());
        if ($extra !== null) {
            return $extra;
        }

        if (in_array($perfil, $this->editores, true)) {
            return PermissoesPerfil::EDICAO;
        }

        if (in_array($perfil, $this->leitores, true)) {
            return PermissoesPerfil::CONSULTA;
        }

        return PermissoesPerfil::NENHUM;
    }

    /** Devolve a explicação quando o registro não pode ser excluído; null quando pode. */
    protected function motivoBloqueioExclusao(Model $registro): ?string
    {
        return null;
    }

    protected function podeEditar(User $user): bool
    {
        return $this->temNivel($user, [PermissoesPerfil::EDICAO]);
    }

    /** Chave do cadastro: CursoPolicy => curso, FichaSaudePolicy => ficha_saude. */
    protected function recurso(): string
    {
        return Str::snake(Str::beforeLast(class_basename($this), 'Policy'));
    }

    /** @param list<string> $niveis */
    private function temNivel(User $user, array $niveis): bool
    {
        foreach ($user->perfisAtivos() as $perfil) {
            $nivel = PermissoesPerfil::nivel($perfil, $this->recurso(), $this->nivelPadrao($perfil));

            if (in_array($nivel, $niveis, true)) {
                return true;
            }
        }

        return false;
    }
}
