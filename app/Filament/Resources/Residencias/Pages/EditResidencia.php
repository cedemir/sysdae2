<?php

namespace App\Filament\Resources\Residencias\Pages;

use App\Filament\Resources\Residencias\ResidenciaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditResidencia extends EditRecord
{
    protected static string $resource = ResidenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
