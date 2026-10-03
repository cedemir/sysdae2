<?php

namespace App\Filament\Resources\Alojamentos\Pages;

use App\Filament\Resources\Alojamentos\AlojamentoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAlojamento extends ViewRecord
{
    protected static string $resource = AlojamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
