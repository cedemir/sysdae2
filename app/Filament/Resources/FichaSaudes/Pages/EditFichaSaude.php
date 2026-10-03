<?php

namespace App\Filament\Resources\FichaSaudes\Pages;

use App\Filament\Resources\FichaSaudes\FichaSaudeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFichaSaude extends EditRecord
{
    protected static string $resource = FichaSaudeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
