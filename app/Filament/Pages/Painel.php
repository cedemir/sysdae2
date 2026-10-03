<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;

/** Página inicial do painel, com o botão para o usuário trocar a própria senha. */
class Painel extends Dashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('alterarSenha')
                ->label('Alterar senha')
                ->icon(Heroicon::OutlinedKey)
                ->url(AlterarSenha::getUrl()),
        ];
    }
}
