<?php

namespace App\Filament\Resources\Apartamentos\Tables;

use App\Models\Apartamento;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ApartamentosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alojamento.nome')
                    ->label('Alojamento')
                    ->sortable(),
                TextColumn::make('andar')
                    ->label('Andar')
                    ->placeholder('-'),
                TextColumn::make('capacidade')
                    ->label('Vagas'),
                TextColumn::make('residencias_count')
                    ->label('Ocupação')
                    ->counts('residencias')
                    ->formatStateUsing(fn ($state, Apartamento $record) => "{$state}/{$record->capacidade}"),
                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('alojamento')
                    ->label('Alojamento')
                    ->relationship('alojamento', 'nome'),
                TernaryFilter::make('ativo')
                    ->label('Situação')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('numero');
    }
}