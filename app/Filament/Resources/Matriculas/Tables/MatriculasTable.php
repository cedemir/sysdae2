<?php

namespace App\Filament\Resources\Matriculas\Tables;

use App\Models\Aluno;
use App\Support\Formatos;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MatriculasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('aluno.nome')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('aluno.cpf')
                    ->label('CPF')
                    ->formatStateUsing(fn (?string $state) => Formatos::cpf($state))
                    ->searchable(),
                TextColumn::make('numero')
                    ->label('Nº matrícula')
                    ->searchable(),
                TextColumn::make('turma.codigo')
                    ->label('Turma'),
                TextColumn::make('turma.curso.nome')
                    ->label('Curso'),
                TextColumn::make('data_matricula')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('situacao')
                    ->label('Situação')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Aluno::SITUACOES[$state] ?? $state),
            ])
            ->filters([
                SelectFilter::make('situacao')
                    ->label('Situação')
                    ->options(Aluno::SITUACOES),
                SelectFilter::make('turma')
                    ->label('Turma')
                    ->relationship('turma', 'codigo'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('data_matricula', 'desc');
    }
}