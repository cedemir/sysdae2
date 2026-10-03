<?php

namespace App\Filament\Resources\Acessos\Pages;

use App\Filament\Resources\Acessos\AcessoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAcesso extends EditRecord
{
    protected static string $resource = AcessoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
