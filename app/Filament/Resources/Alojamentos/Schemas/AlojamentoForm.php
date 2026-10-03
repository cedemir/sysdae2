<?php

namespace App\Filament\Resources\Alojamentos\Schemas;

use App\Models\Alojamento;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AlojamentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do alojamento')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('localizacao')
                            ->label('Localização')
                            ->maxLength(200),
                        Select::make('publico')
                            ->label('Público')
                            ->options(Alojamento::PUBLICOS)
                            ->required(),
                        Toggle::make('ativo')
                            ->label('Alojamento ativo')
                            ->default(true),
                        Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}