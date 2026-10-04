<?php

namespace App\Filament\Resources\Perfis\Pages;

use App\Filament\Resources\Perfis\PerfilResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPerfis extends ListRecords
{
    protected static string $resource = PerfilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
