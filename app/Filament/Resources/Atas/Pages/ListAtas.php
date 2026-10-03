<?php

namespace App\Filament\Resources\Atas\Pages;

use App\Filament\Resources\Atas\AtaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtas extends ListRecords
{
    protected static string $resource = AtaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
