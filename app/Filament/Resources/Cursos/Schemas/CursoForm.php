<?php

namespace App\Filament\Resources\Cursos\Schemas;

use App\Models\Curso;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CursoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do curso')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('sigla')
                            ->label('Sigla')
                            ->maxLength(20),
                        Select::make('nivel')
                            ->label('Nível')
                            ->options(Curso::NIVEIS)
                            ->required(),
                        TextInput::make('duracao_anos')
                            ->label('Duração (anos)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10),
                        Toggle::make('ativo')
                            ->label('Curso ativo')
                            ->default(true),
                    ]),
            ]);
    }
}