<?php

namespace App\Filament\Resources\Regimes\Pages;

use App\Filament\Resources\Regimes\RegimeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRegime extends EditRecord
{
    protected static string $resource = RegimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
