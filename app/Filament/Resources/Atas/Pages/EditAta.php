<?php

namespace App\Filament\Resources\Atas\Pages;

use App\Filament\Resources\Atas\AtaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAta extends EditRecord
{
    protected static string $resource = AtaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
