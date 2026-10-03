<?php

namespace App\Filament\Resources\Residencias\Pages;

use App\Filament\Resources\Residencias\ResidenciaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResidencias extends ListRecords
{
    protected static string $resource = ResidenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
