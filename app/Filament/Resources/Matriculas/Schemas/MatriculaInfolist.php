<?php

namespace App\Filament\Resources\Matriculas\Schemas;

use App\Support\Formatos;
use App\Models\Aluno;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MatriculaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('aluno.nome')
                    ->label('Aluno'),
                TextEntry::make('aluno.cpf')
                    ->label('CPF')
                    ->formatStateUsing(fn (?string $state) => Formatos::cpf($state)),
                TextEntry::make('turma.codigo')
                    ->label('Turma'),
                TextEntry::make('turma.curso.nome')
                    ->label('Curso'),
                TextEntry::make('numero')
                    ->label('Número'),
                TextEntry::make('data_matricula')
                    ->label('Data da matrícula')
                    ->date('d/m/Y'),
                TextEntry::make('situacao')
                    ->label('Situação')
                    ->formatStateUsing(fn (?string $state) => Aluno::SITUACOES[$state] ?? $state),
                TextEntry::make('observacoes')
                    ->label('Observações')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
            ]);
    }
}
