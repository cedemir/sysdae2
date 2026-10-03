<?php

namespace App\Filament\Resources\Alojamentos\Pages;

use App\Filament\Resources\Alojamentos\AlojamentoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAlojamento extends EditRecord
{
    protected static string $resource = AlojamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
