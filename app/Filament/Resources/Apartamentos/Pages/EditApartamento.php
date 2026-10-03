<?php

namespace App\Filament\Resources\Apartamentos\Pages;

use App\Filament\Resources\Apartamentos\ApartamentoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditApartamento extends EditRecord
{
    protected static string $resource = ApartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
