<?php

namespace App\Filament\Resources\Perfis\Pages;

use App\Filament\Resources\Perfis\PerfilResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPerfil extends EditRecord
{
    protected static string $resource = PerfilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
