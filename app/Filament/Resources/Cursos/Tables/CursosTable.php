<?php

namespace App\Filament\Resources\Cursos\Tables;

use App\Models\Curso;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CursosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sigla')
                    ->label('Sigla'),
                TextColumn::make('nivel')
                    ->label('Nível')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Curso::NIVEIS[$state] ?? $state),
                TextColumn::make('duracao_anos')
                    ->label('Duração (anos)'),
                TextColumn::make('turmas_count')
                    ->label('Turmas')
                    ->counts('turmas'),
                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('ativo')
                    ->label('Situação')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('nome');
    }
}