<?php

namespace App\Filament\Resources\Regimes\Tables;

use App\Models\Regime;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegimesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('aplica_se_a')
                    ->label('Aplica-se a')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Regime::APLICACOES[$state] ?? $state),
                TextColumn::make('residencias_count')
                    ->label('Residentes')
                    ->counts('residencias'),
                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('nome');
    }
}