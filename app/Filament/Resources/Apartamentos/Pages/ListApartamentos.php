<?php

namespace App\Filament\Resources\Apartamentos\Pages;

use App\Filament\Resources\Apartamentos\ApartamentoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApartamentos extends ListRecords
{
    protected static string $resource = ApartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
