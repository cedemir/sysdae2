<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** Perfis antes de salvar, para registrar a mudança de perfil na auditoria. */
    protected array $perfisAntes = [];

    protected function beforeSave(): void
    {
        $this->perfisAntes = $this->getRecord()->roles()->pluck('name')->all();
    }

    protected function afterSave(): void
    {
        $usuario = $this->getRecord();

        \App\Support\AuditoriaDePerfil::registrar($usuario, $this->perfisAntes, $usuario->roles()->pluck('name')->all());
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
