<?php

namespace App\Filament\Resources\Atas\Pages;

use App\Filament\Resources\Atas\AtaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAta extends ViewRecord
{
    protected static string $resource = AtaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(), // só aparece para quem pode editar
        ];
    }
}