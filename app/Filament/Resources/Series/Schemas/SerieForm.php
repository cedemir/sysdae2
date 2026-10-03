<?php

namespace App\Filament\Resources\Series\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SerieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da série')
                    ->columns(2)
                    ->schema([
                        TextInput::make('ordem')
                            ->label('Ordem (1 a 10)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('nome')
                            ->label('Nome')
                            ->helperText('Exemplo: 1º Ano')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Toggle::make('ativa')
                            ->label('Série ativa')
                            ->default(true),
                    ]),
            ]);
    }
}