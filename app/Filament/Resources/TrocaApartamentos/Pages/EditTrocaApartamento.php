<?php

namespace App\Filament\Resources\TrocaApartamentos\Pages;

use App\Filament\Resources\TrocaApartamentos\TrocaApartamentoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrocaApartamento extends EditRecord
{
    protected static string $resource = TrocaApartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
