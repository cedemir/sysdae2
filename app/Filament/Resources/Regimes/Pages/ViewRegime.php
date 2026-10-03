<?php

namespace App\Filament\Resources\Regimes\Pages;

use App\Filament\Resources\Regimes\RegimeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRegime extends ViewRecord
{
    protected static string $resource = RegimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
