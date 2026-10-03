<?php

namespace App\Filament\Resources\Series\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ordem')
                    ->label('Ordem')
                    ->sortable(),
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('turmas_count')
                    ->label('Turmas')
                    ->counts('turmas'),
                IconColumn::make('ativa')
                    ->label('Ativa')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('ordem');
    }
}