<?php

namespace App\Filament\Resources\Atas\Schemas;

use App\Models\Aluno;
use App\Support\Formatos;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ata de reunião')
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero')
                            ->label('Nº da ata')
                            ->helperText('Inclua o ano para reaproveitar a numeração. Exemplo: 012/2026.')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        DatePicker::make('data_reuniao')
                            ->label('Data da reunião')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(fn () => now()->toDateString())
                            ->required(),
                        TextInput::make('assunto')
                            ->label('Assunto')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),
                        Textarea::make('participantes')
                            ->label('Participantes')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('alunos')
                            ->label('Alunos citados na ata')
                            ->relationship('alunos', 'nome')
                            ->getOptionLabelFromRecordUsing(
                                fn (Aluno $record) => $record->nome . ' (' . Formatos::cpf($record->cpf) . ')'
                            )
                            ->multiple()
                            ->searchable(['nome', 'cpf'])
                            ->helperText('Pode citar vários alunos. Pode ficar vazio, para reuniões gerais.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Conteúdo')
                    ->schema([
                        Textarea::make('pauta')
                            ->label('Pauta')
                            ->rows(4),
                        Textarea::make('deliberacoes')
                            ->label('Deliberações')
                            ->rows(6),
                        Textarea::make('encaminhamentos')
                            ->label('Encaminhamentos')
                            ->rows(4),
                    ]),
            ]);
    }
}