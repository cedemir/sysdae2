<?php

namespace App\Filament\Resources\Alojamentos\Pages;

use App\Filament\Resources\Alojamentos\AlojamentoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlojamentos extends ListRecords
{
    protected static string $resource = AlojamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
