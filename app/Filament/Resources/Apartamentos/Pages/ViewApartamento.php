<?php

namespace App\Filament\Resources\Apartamentos\Pages;

use App\Filament\Resources\Apartamentos\ApartamentoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewApartamento extends ViewRecord
{
    protected static string $resource = ApartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
