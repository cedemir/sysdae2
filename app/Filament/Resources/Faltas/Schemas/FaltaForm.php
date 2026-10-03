<?php

namespace App\Filament\Resources\Faltas\Schemas;

use App\Models\Aluno;
use App\Models\Falta;
use App\Support\Formatos;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FaltaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Falta na residência')
                    ->columns(2)
                    ->schema([
                        Select::make('aluno_id')
                            ->label('Aluno')
                            ->relationship('aluno', 'nome')
                            ->getOptionLabelFromRecordUsing(
                                fn (Aluno $record) => $record->nome . ' (' . Formatos::cpf($record->cpf) . ')'
                            )
                            ->searchable(['nome', 'cpf'])
                            ->required(),
                        DatePicker::make('data_falta')
                            ->label('Data da falta')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(fn () => now()->toDateString())
                            ->maxDate(now())
                            ->required()
                            ->rules([
                                // Compara apenas o dia, qualquer que seja o formato em que a data chegue.
                                fn (Get $get, ?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                                    if (blank($value) || blank($get('aluno_id'))) {
                                        return;
                                    }

                                    $jaExiste = Falta::query()
                                        ->where('aluno_id', $get('aluno_id'))
                                        ->whereDate('data_falta', Carbon::parse($value)->toDateString())
                                        ->when($record, fn ($consulta) => $consulta->whereKeyNot($record->getKey()))
                                        ->exists();

                                    if ($jaExiste) {
                                        $fail('Já existe uma falta registrada para este aluno nesta data.');
                                    }
                                },
                            ]),
                        Toggle::make('justificada')
                            ->label('Falta justificada'),
                        Textarea::make('observacao')
                            ->label('Observação')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
