<?php

namespace App\Filament\Resources\Pernoites\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PernoitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('aluno.nome')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('data_pernoite')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                IconColumn::make('parcial')
                    ->label('Parcial')
                    ->boolean(),
                TextColumn::make('forma_autorizacao')
                    ->label('Forma')
                    ->placeholder('-'),
                TextColumn::make('quem_autorizou')
                    ->label('Autorizado por')
                    ->searchable(),
                TextColumn::make('justificativa')
                    ->label('Justificativa')
                    ->limit(50),
            ])
            ->filters([
                SelectFilter::make('aluno')
                    ->label('Aluno')
                    ->relationship('aluno', 'nome')
                    ->searchable(),
                TernaryFilter::make('parcial')
                    ->label('Parcial')
                    ->trueLabel('Somente parciais')
                    ->falseLabel('Não parciais'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('data_pernoite', 'desc');
    }
}