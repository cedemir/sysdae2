<?php

namespace App\Filament\Resources\Pernoites\Pages;

use App\Filament\Resources\Pernoites\PernoiteResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPernoite extends ViewRecord
{
    protected static string $resource = PernoiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(), // só aparece para quem pode editar
        ];
    }
}