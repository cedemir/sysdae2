<?php

namespace App\Filament\Resources\Acessos\Schemas;

use App\Models\Acesso;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AcessoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Acesso do perfil a um cadastro')
                    ->description('O administrador sempre tem acesso a tudo e não depende desta tela.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nome')
                            ->label('Perfil / cadastro')
                            ->formatStateUsing(fn (Acesso $record) => $record->nome)
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('nivel')
                            ->label('Acesso')
                            ->options(fn (Acesso $record) => $record->niveisPermitidos())
                            ->required(),
                    ]),
            ]);
    }
}