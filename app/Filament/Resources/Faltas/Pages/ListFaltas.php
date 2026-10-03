<?php

namespace App\Filament\Resources\Faltas\Pages;

use App\Filament\Resources\Faltas\FaltaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFaltas extends ListRecords
{
    protected static string $resource = FaltaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
