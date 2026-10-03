<?php

namespace App\Filament\Resources\Residencias\Schemas;

use App\Models\Aluno;
use App\Models\Apartamento;
use App\Models\Regime;
use App\Models\Residencia;
use App\Support\Formatos;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ResidenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Residência do aluno')
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
                            ->disabled(fn (?Model $record) => $record !== null)
                            ->unique(ignoreRecord: true)
                            ->validationMessages(['unique' => 'Este aluno já possui registro de residência. Edite o existente.']),
                        Select::make('categoria')
                            ->label('Categoria')
                            ->options(Residencia::CATEGORIAS)
                            ->required()
                            ->live(),
                        Select::make('apartamento_id')
                            ->label('Apartamento')
                            ->relationship(
                                name: 'apartamento',
                                titleAttribute: 'numero',
                                modifyQueryUsing: fn (Builder $query) => $query->where('ativo', true)->with('alojamento'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Apartamento $record) => "{$record->numero} - {$record->alojamento->nome} ({$record->ocupacao()}/{$record->capacidade})"
                            )
                            ->searchable()
                            ->preload()
                            ->helperText('Obrigatório para residentes. Entre parênteses: ocupação atual / vagas.')
                            ->required(fn (Get $get) => $get('categoria') === 'residente')
                            ->rules([
                                fn (Get $get, ?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                                    if (blank($value)) {
                                        return;
                                    }
                                    if ($record && (int) $record->apartamento_id === (int) $value) {
                                        return; // o apartamento não mudou
                                    }

                                    $apto = Apartamento::with('alojamento')->find($value);
                                    if (! $apto) {
                                        return;
                                    }
                                    if (! $apto->ativo) {
                                        $fail("O apartamento {$apto->numero} está inativo.");
                                        return;
                                    }

                                    $ocupacao = $apto->ocupacao();
                                    if ($ocupacao >= $apto->capacidade) {
                                        $fail("O apartamento {$apto->numero} está lotado ({$ocupacao}/{$apto->capacidade}).");
                                        return;
                                    }

                                    $aluno = Aluno::find($get('aluno_id') ?? $record?->aluno_id);
                                    $publico = $apto->alojamento->publico;
                                    // Para quem escolheu "Prefiro não dizer" não há bloqueio automático: a equipe decide.
                                    if ($aluno && in_array($aluno->sexo, ['masculino', 'feminino'], true)
                                        && $publico !== 'misto' && $publico !== $aluno->sexo) {
                                        $fail("O alojamento \"{$apto->alojamento->nome}\" é destinado ao público {$publico}, incompatível com o sexo do aluno.");
                                    }
                                },
                            ]),
                        Select::make('regime_id')
                            ->label('Regime')
                            ->relationship(
                                name: 'regime',
                                titleAttribute: 'nome',
                                modifyQueryUsing: fn (Builder $query) => $query->where('ativo', true),
                            )
                            ->searchable()
                            ->preload()
                            ->rules([
                                fn (Get $get, ?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                                    if (blank($value)) {
                                        return;
                                    }
                                    $regime = Regime::find($value);
                                    $categoria = $get('categoria');
                                    if ($regime && filled($categoria) && ! $regime->aceita($categoria)) {
                                        $fail("O regime \"{$regime->nome}\" não se aplica à categoria selecionada.");
                                    }
                                },
                            ]),
                        DatePicker::make('data_entrada')
                            ->label('Data de entrada na residência')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Textarea::make('ocorrencias')
                            ->label('Ocorrências')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}