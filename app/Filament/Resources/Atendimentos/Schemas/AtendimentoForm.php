<?php

namespace App\Filament\Resources\Atendimentos\Schemas;

use App\Models\Aluno;
use App\Models\Atendimento;
use App\Support\Formatos;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtendimentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Atendimento')
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
                        Select::make('forma')
                            ->label('Forma de atendimento')
                            ->options(Atendimento::FORMAS)
                            ->required(),
                        DatePicker::make('data_atendimento')
                            ->label('Data')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(fn () => now()->toDateString())
                            ->required(),
                        TimePicker::make('hora_atendimento')
                            ->label('Hora')
                            ->seconds(false),
                        TextInput::make('servidores')
                            ->label('Servidores responsáveis')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),
                        Textarea::make('relato')
                            ->label('Relato do atendimento')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('outras_observacoes')
                            ->label('Outras observações')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('sigiloso')
                            ->label('Atendimento sigiloso')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('História de vida e encaminhamentos')
                    ->schema([
                        Textarea::make('historia_vida')
                            ->label('História de vida')
                            ->rows(5),
                        Textarea::make('encaminhamentos')
                            ->label('Encaminhamentos')
                            ->rows(4),
                    ]),
            ]);
    }
}