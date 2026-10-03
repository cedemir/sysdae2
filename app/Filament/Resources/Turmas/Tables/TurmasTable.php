<?php

namespace App\Filament\Resources\Turmas\Tables;

use App\Models\Turma;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TurmasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('curso.nome')
                    ->label('Curso')
                    ->sortable(),
                TextColumn::make('serie.nome')
                    ->label('Série')
                    ->placeholder('-'),
                TextColumn::make('ano_letivo')
                    ->label('Ano letivo')
                    ->sortable(),
                TextColumn::make('turno')
                    ->label('Turno')
                    ->formatStateUsing(fn (?string $state) => Turma::TURNOS[$state] ?? $state),
                TextColumn::make('matriculas_count')
                    ->label('Alunos')
                    ->counts('matriculas'),
                IconColumn::make('ativa')
                    ->label('Ativa')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('curso')
                    ->label('Curso')
                    ->relationship('curso', 'nome'),
                TernaryFilter::make('ativa')
                    ->label('Situação')
                    ->trueLabel('Ativas')
                    ->falseLabel('Inativas'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('ano_letivo', 'desc');
    }
}