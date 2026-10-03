<?php

namespace App\Filament\Resources\FichaSaudes\Pages;

use App\Filament\Resources\FichaSaudes\FichaSaudeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFichaSaude extends ViewRecord
{
    protected static string $resource = FichaSaudeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(), // só aparece para quem pode editar
        ];
    }
}