<?php

namespace App\Filament\Resources\Acessos\Pages;

use App\Filament\Resources\Acessos\AcessoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcessos extends ListRecords
{
    protected static string $resource = AcessoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
