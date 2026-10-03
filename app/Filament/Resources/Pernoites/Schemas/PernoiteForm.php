<?php

namespace App\Filament\Resources\Pernoites\Schemas;

use App\Models\Aluno;
use App\Support\Formatos;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PernoiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Autorização de pernoite')
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
                        DatePicker::make('data_pernoite')
                            ->label('Data')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(fn () => now()->toDateString())
                            ->required(),
                        Toggle::make('parcial')
                            ->label('Autorização parcial'),
                        TextInput::make('forma_autorizacao')
                            ->label('Forma de autorização')
                            ->helperText('Exemplo: presencial, telefone, e-mail, documento.')
                            ->maxLength(50),
                        TextInput::make('quem_autorizou')
                            ->label('Quem autorizou')
                            ->required()
                            ->maxLength(100),
                        Textarea::make('justificativa')
                            ->label('Justificativa')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}