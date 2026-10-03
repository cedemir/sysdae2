<?php

namespace App\Filament\Resources\FichaSaudes\Pages;

use App\Filament\Resources\FichaSaudes\FichaSaudeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFichaSaudes extends ListRecords
{
    protected static string $resource = FichaSaudeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
