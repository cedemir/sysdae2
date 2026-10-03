<?php

namespace App\Filament\Resources\Apartamentos\Schemas;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class ApartamentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do apartamento')
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número')
                            ->helperText('Único em toda a residência. É salvo em maiúsculas.')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state) => mb_strtoupper(trim((string) $state))),
                        Select::make('alojamento_id')
                            ->label('Alojamento')
                            ->relationship('alojamento', 'nome')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('andar')
                            ->label('Andar')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50),
                        TextInput::make('capacidade')
                            ->label('Capacidade (vagas)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->required()
                            ->rules([
                                fn (?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                    if ($record) {
                                        $moradores = $record->residencias()->count();
                                        if ((int) $value < $moradores) {
                                            $fail("O apartamento tem {$moradores} morador(es); a capacidade não pode ser menor que isso.");
                                        }
                                    }
                                },
                            ]),
                        Toggle::make('ativo')
                            ->label('Apartamento ativo')
                            ->default(true)
                            ->rules([
                                fn (?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                    if ($record && ! $value) {
                                        $moradores = $record->residencias()->count();
                                        if ($moradores > 0) {
                                            $fail("Não é possível inativar: há {$moradores} morador(es) no apartamento.");
                                        }
                                    }
                                },
                            ]),
                    ]),
            ]);
    }
}