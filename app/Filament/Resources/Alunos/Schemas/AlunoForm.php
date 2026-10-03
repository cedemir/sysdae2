<?php

namespace App\Filament\Resources\Alunos\Schemas;

use App\Models\Aluno;
use App\Rules\Cpf;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class AlunoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados pessoais')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('foto_path')
                            ->label('Foto')
                            ->image()
                            ->imageEditor()
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth(600)
                            ->imageResizeTargetHeight(600)
                            ->disk('public')
                            ->directory('fotos-alunos')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('cpf')
                            ->label('CPF')
                            ->required()
                            ->mask('999.999.999-99')
                            ->placeholder('000.000.000-00')
                            ->rule(fn (?Model $record) => new Cpf($record?->getKey()))
                            ->dehydrateStateUsing(fn (?string $state) => preg_replace('/\D/', '', (string) $state)),
                        Select::make('sexo')
                            ->label('Sexo')
                            ->options(Aluno::SEXOS)
                            ->required(),
                        TextInput::make('municipio')
                            ->label('Município')
                            ->maxLength(100),
                    ]),

                Section::make('Contatos')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(150),
                        TextInput::make('telefone_estudante')
                            ->label('Telefone do estudante')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('nome_responsaveis')
                            ->label('Responsáveis')
                            ->maxLength(200),
                        TextInput::make('telefone_familia')
                            ->label('Telefone da família')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('contato_emergencia')
                            ->label('Contato de emergência')
                            ->maxLength(150)
                            ->columnSpanFull(),
                    ]),

                Section::make('Situação e benefícios')
                    ->columns(2)
                    ->schema([
                        Select::make('situacao')
                            ->label('Situação')
                            ->options(Aluno::SITUACOES)
                            ->default('cursando')
                            ->required(),
                        Select::make('programa_beneficios')
                            ->label('Programa de benefícios')
                            ->options(Aluno::PROGRAMAS)
                            ->default('nao_recebe')
                            ->required(),
                        Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}