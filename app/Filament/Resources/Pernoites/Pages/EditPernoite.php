<?php

namespace App\Filament\Resources\Pernoites\Pages;

use App\Filament\Resources\Pernoites\PernoiteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPernoite extends EditRecord
{
    protected static string $resource = PernoiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
