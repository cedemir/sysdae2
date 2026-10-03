<?php

namespace App\Filament\Resources\FichaSaudes\Schemas;

use App\Models\Aluno;
use App\Models\FichaSaude;
use App\Support\Formatos;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class FichaSaudeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação')
                    ->description('Dados pessoais sensíveis: uso restrito à equipe autorizada.')
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
                            ->validationMessages(['unique' => 'Este aluno já possui ficha de saúde. Edite a existente.']),
                        Select::make('tipo_sanguineo')
                            ->label('Tipo sanguíneo')
                            ->options(FichaSaude::TIPOS_SANGUINEOS)
                            ->default('nao_informado')
                            ->required(),
                        TextInput::make('cartao_sus')
                            ->label('Cartão SUS')
                            ->mask('999 9999 9999 9999')
                            ->dehydrateStateUsing(fn (?string $state) => preg_replace('/\D/', '', (string) $state) ?: null)
                            ->rules([
                                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                    if (filled($value) && strlen(preg_replace('/\D/', '', (string) $value)) !== 15) {
                                        $fail('O cartão SUS deve ter 15 dígitos.');
                                    }
                                },
                            ]),
                        TextInput::make('plano_saude')
                            ->label('Plano de saúde')
                            ->maxLength(100),
                        TextInput::make('unidade_referencia')
                            ->label('Unidade de saúde de referência')
                            ->maxLength(150)
                            ->columnSpanFull(),
                    ]),

                Section::make('Saúde e nutrição')
                    ->columns(2)
                    ->schema([
                        Textarea::make('alergias')
                            ->label('Alergias')
                            ->rows(3),
                        Textarea::make('restricoes_alimentares')
                            ->label('Restrições alimentares')
                            ->rows(3),
                        Textarea::make('condicoes_saude')
                            ->label('Condições de saúde')
                            ->rows(3),
                        Textarea::make('medicamentos_uso')
                            ->label('Medicamentos de uso contínuo')
                            ->rows(3),
                        Textarea::make('necessidades_especiais')
                            ->label('Necessidades especiais')
                            ->rows(3),
                        Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(3),
                    ]),
            ]);
    }
}