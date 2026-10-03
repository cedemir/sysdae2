<?php

namespace App\Filament\Resources\Turmas\Schemas;

use App\Models\Turma;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TurmaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da turma')
                    ->columns(2)
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->helperText('Exemplo: INFO3A-2026. É salvo em maiúsculas.')
                            ->required()
                            ->maxLength(30)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state) => mb_strtoupper(trim((string) $state))),
                        Select::make('curso_id')
                            ->label('Curso')
                            ->relationship('curso', 'nome')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('serie_id')
                            ->label('Série')
                            ->relationship('serie', 'nome')
                            ->searchable()
                            ->preload(),
                        TextInput::make('ano_letivo')
                            ->label('Ano letivo')
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->default((int) date('Y'))
                            ->required(),
                        Select::make('turno')
                            ->label('Turno')
                            ->options(Turma::TURNOS)
                            ->default('integral')
                            ->required(),
                        Toggle::make('ativa')
                            ->label('Turma ativa')
                            ->default(true),
                    ]),
            ]);
    }
}