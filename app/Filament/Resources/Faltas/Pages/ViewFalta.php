<?php

namespace App\Filament\Resources\Faltas\Pages;

use App\Filament\Resources\Faltas\FaltaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFalta extends ViewRecord
{
    protected static string $resource = FaltaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(), // só aparece para quem pode editar
        ];
    }
}