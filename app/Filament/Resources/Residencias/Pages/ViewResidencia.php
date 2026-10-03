<?php

namespace App\Filament\Resources\Residencias\Pages;

use App\Filament\Resources\Residencias\ResidenciaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewResidencia extends ViewRecord
{
    protected static string $resource = ResidenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
