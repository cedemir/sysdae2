<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Controle de acesso do usuário: campo "ativo" e encerramento das sessões ao inativar ou excluir.
 */
trait ControlaAcesso
{
    public function initializeControlaAcesso(): void
    {
        $this->mergeFillable(['ativo']);
        $this->mergeCasts(['ativo' => 'boolean']);

        // Usuário novo nasce ativo (o banco também tem esse padrão).
        if (! array_key_exists('ativo', $this->attributes)) {
            $this->attributes['ativo'] = true;
        }
    }

    protected static function bootControlaAcesso(): void
    {
        static::updated(function (User $usuario): void {
            $estavaAtivo = (bool) $usuario->getOriginal('ativo');

            if ($estavaAtivo && ! $usuario->ativo) {
                $usuario->encerrarAcessos();
            }
        });

        static::deleted(fn (User $usuario) => $usuario->encerrarAcessos());
    }

    /** Derruba as sessões abertas e invalida o "lembrar de mim". */
    public function encerrarAcessos(): void
    {
        DB::table(config('session.table', 'sessions'))->where('user_id', $this->getKey())->delete();
        DB::table('users')->where('id', $this->getKey())->update(['remember_token' => Str::random(60)]);
    }
}