<?php

namespace App\Filament\Resources\TrocaApartamentos\Pages;

use App\Filament\Resources\TrocaApartamentos\TrocaApartamentoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrocaApartamentos extends ListRecords
{
    protected static string $resource = TrocaApartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
