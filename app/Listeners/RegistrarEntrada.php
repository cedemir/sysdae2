<?php

namespace App\Listeners;

use App\Models\Auditoria;
use Illuminate\Auth\Events\Login;

class RegistrarEntrada
{
    public function handle(Login $evento): void
    {
        Auditoria::registrar('entrou', 'Acesso', (int) $evento->user->getAuthIdentifier(), $evento->user->email ?? null);
    }
}