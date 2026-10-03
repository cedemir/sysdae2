<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $usuario = $this->getRecord();

        \App\Support\AuditoriaDePerfil::registrar($usuario, [], $usuario->roles()->pluck('name')->all());
    }
}
