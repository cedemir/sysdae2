<?php

namespace App\Filament\Resources\Alunos\Schemas;

use App\Models\Aluno;
use App\Support\Formatos;
use App\Support\Visualizacao;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AlunoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados pessoais')->columns(2)->schema([
                ImageEntry::make('foto_path')->label('Foto')->disk('public')->circular()->columnSpanFull(),
                TextEntry::make('nome')->label('Nome'),
                TextEntry::make('cpf')->label('CPF')
                    ->formatStateUsing(fn (?string $state) => Formatos::cpf($state)),
                TextEntry::make('sexo')->label('Sexo')
                    ->formatStateUsing(fn (?string $state) => Aluno::SEXOS[$state] ?? $state),
                TextEntry::make('municipio')->label('Município')->placeholder('-'),
            ]),

            Section::make('Curso e residência')->columns(2)->schema([
                TextEntry::make('curso_atual')->label('Curso')->placeholder('Sem matrícula')
                    ->state(fn ($record) => $record->matriculaAtual?->turma?->curso?->nome),
                TextEntry::make('turma_atual')->label('Turma')->placeholder('-')
                    ->state(fn ($record) => $record->matriculaAtual?->turma?->codigo),
                TextEntry::make('matricula_atual')->label('Nº de matrícula')->placeholder('-')
                    ->state(fn ($record) => $record->matriculaAtual?->numero),
                TextEntry::make('situacao')->label('Situação')->badge()
                    ->formatStateUsing(fn (?string $state) => Aluno::SITUACOES[$state] ?? $state),
                TextEntry::make('apartamento_atual')->label('Apartamento')->placeholder('-')
                    ->state(fn ($record) => $record->residencia?->apartamento?->numero),
                TextEntry::make('programa_beneficios')->label('Programa de benefícios')
                    ->formatStateUsing(fn (?string $state) => Aluno::PROGRAMAS[$state] ?? $state),
            ]),

            Section::make('Contatos')->columns(2)->schema([
                TextEntry::make('email')->label('E-mail')->placeholder('-'),
                TextEntry::make('telefone_estudante')->label('Telefone do estudante')->placeholder('-'),
                TextEntry::make('nome_responsaveis')->label('Responsáveis')->placeholder('-'),
                TextEntry::make('telefone_familia')->label('Telefone da família')->placeholder('-'),
                TextEntry::make('contato_emergencia')->label('Contato de emergência')->placeholder('-')
                    ->columnSpanFull(),
                Visualizacao::texto('observacoes', 'Observações'),
            ]),
        ]);
    }
}