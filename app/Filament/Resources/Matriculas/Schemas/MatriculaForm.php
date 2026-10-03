<?php

namespace App\Filament\Resources\Matriculas\Schemas;

use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Turma;
use App\Support\Formatos;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

class MatriculaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Matrícula')
                    ->columns(2)
                    ->schema([
                        Select::make('aluno_id')
                            ->label('Aluno')
                            ->relationship('aluno', 'nome')
                            ->getOptionLabelFromRecordUsing(
                                fn (Aluno $record) => $record->nome . ' (' . Formatos::cpf($record->cpf) . ')'
                            )
                            ->searchable(['nome', 'cpf'])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                // Sugere o número de matrícula que o aluno já usa.
                                if ($state && blank($get('numero'))) {
                                    $anterior = Matricula::where('aluno_id', $state)
                                        ->orderByDesc('data_matricula')
                                        ->orderByDesc('id')
                                        ->first();
                                    if ($anterior) {
                                        $set('numero', $anterior->numero);
                                    }
                                }
                            }),
                        TextInput::make('numero')
                            ->label('Nº de matrícula')
                            ->helperText('O aluno mantém o mesmo número em todas as matrículas.')
                            ->required()
                            ->maxLength(20)
                            ->dehydrateStateUsing(fn (?string $state) => mb_strtoupper(trim((string) $state)))
                            ->rules([
                                fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    $pertenceAOutro = Matricula::query()
                                        ->where('numero', mb_strtoupper(trim((string) $value)))
                                        ->where('aluno_id', '!=', $get('aluno_id'))
                                        ->exists();
                                    if ($pertenceAOutro) {
                                        $fail('Este número de matrícula já pertence a outro aluno.');
                                    }
                                },
                            ]),
                        Select::make('turma_id')
                            ->label('Turma')
                            ->relationship('turma', 'codigo')
                            ->getOptionLabelFromRecordUsing(
                                fn (Turma $record) => $record->codigo . ' (' . $record->curso->nome . ')'
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('aluno_id', $get('aluno_id')),
                            )
                            ->validationMessages(['unique' => 'O aluno já possui matrícula nesta turma.'])
                            ->rules([
                                fn (?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                    $turma = Turma::find($value);
                                    $mudou = ! $record || (int) $record->turma_id !== (int) $value;
                                    if ($turma && ! $turma->ativa && $mudou) {
                                        $fail('A turma selecionada está inativa.');
                                    }
                                },
                            ]),
                        DatePicker::make('data_matricula')
                            ->label('Data da matrícula')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(fn () => now()->toDateString())
                            ->required(),
                        Select::make('situacao')
                            ->label('Situação')
                            ->options(Aluno::SITUACOES)
                            ->default('cursando')
                            ->required()
                            ->rules([
                                fn (Get $get, ?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                                    if ($value !== 'cursando' || blank($get('aluno_id'))) {
                                        return;
                                    }
                                    $jaCursando = Matricula::query()
                                        ->where('aluno_id', $get('aluno_id'))
                                        ->where('situacao', 'cursando')
                                        ->when($record, fn ($consulta) => $consulta->whereKeyNot($record->getKey()))
                                        ->exists();
                                    if ($jaCursando) {
                                        $fail('O aluno já tem uma matrícula em andamento (cursando). '
                                            . 'Encerre-a como transferido, trancamento ou formado antes.');
                                    }
                                },
                            ]),
                        Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}