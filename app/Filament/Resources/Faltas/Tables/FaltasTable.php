<?php

namespace App\Filament\Resources\Faltas\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FaltasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('aluno.nome')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('data_falta')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                IconColumn::make('justificada')
                    ->label('Justificada')
                    ->boolean(),
                TextColumn::make('observacao')
                    ->label('Observação')
                    ->limit(50)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('aluno')
                    ->label('Aluno')
                    ->relationship('aluno', 'nome')
                    ->searchable(),
                TernaryFilter::make('justificada')
                    ->label('Justificada')
                    ->trueLabel('Justificadas')
                    ->falseLabel('Não justificadas'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('data_falta', 'desc');
    }
}