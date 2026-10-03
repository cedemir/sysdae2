<?php

namespace App\Filament\Resources\Alojamentos\Tables;

use App\Models\Alojamento;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AlojamentosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('localizacao')
                    ->label('Localização')
                    ->placeholder('-'),
                TextColumn::make('publico')
                    ->label('Público')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Alojamento::PUBLICOS[$state] ?? $state),
                TextColumn::make('apartamentos_count')
                    ->label('Apartamentos')
                    ->counts('apartamentos'),
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