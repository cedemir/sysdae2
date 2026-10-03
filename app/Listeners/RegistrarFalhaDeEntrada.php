<?php

namespace App\Listeners;

use App\Models\Auditoria;
use Illuminate\Auth\Events\Failed;

class RegistrarFalhaDeEntrada
{
    public function handle(Failed $evento): void
    {
        // Nunca grava a senha digitada, apenas o e-mail informado.
        Auditoria::registrar('falhou', 'Acesso', null, (string) ($evento->credentials['email'] ?? 'desconhecido'));
    }
}