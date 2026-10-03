<?php

namespace App\Filament\Resources\Regimes\Schemas;

use App\Models\Regime;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class RegimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do regime')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nome')
                            ->label('Nome')
                            ->helperText('Exemplo: Integral, Segunda a sexta.')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        Select::make('aplica_se_a')
                            ->label('Aplica-se a')
                            ->options(Regime::APLICACOES)
                            ->default('todos')
                            ->required()
                            ->rules([
                                fn (?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                    if ($record && $value !== 'todos') {
                                        $incompativeis = $record->residencias()->where('categoria', '!=', $value)->count();
                                        if ($incompativeis > 0) {
                                            $fail("{$incompativeis} aluno(s) de outra categoria usam este regime. Remova o regime deles antes de restringir.");
                                        }
                                    }
                                },
                            ]),
                        Textarea::make('descricao')
                            ->label('Descrição / regras')
                            ->rows(5)
                            ->columnSpanFull(),
                        Toggle::make('ativo')
                            ->label('Regime ativo')
                            ->default(true),
                    ]),
            ]);
    }
}