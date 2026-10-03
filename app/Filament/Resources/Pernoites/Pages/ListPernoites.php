<?php

namespace App\Filament\Resources\Pernoites\Pages;

use App\Filament\Resources\Pernoites\PernoiteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPernoites extends ListRecords
{
    protected static string $resource = PernoiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
