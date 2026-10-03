<?php

namespace App\Filament\Resources\Faltas\Pages;

use App\Filament\Resources\Faltas\FaltaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFalta extends EditRecord
{
    protected static string $resource = FaltaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
