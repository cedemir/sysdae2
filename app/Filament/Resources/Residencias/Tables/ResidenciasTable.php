<?php

namespace App\Filament\Resources\Residencias\Tables;

use App\Models\Residencia;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResidenciasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('aluno.nome')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categoria')
                    ->label('Categoria')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Residencia::CATEGORIAS[$state] ?? $state),
                TextColumn::make('apartamento.numero')
                    ->label('Apartamento')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('apartamento.alojamento.nome')
                    ->label('Alojamento')
                    ->placeholder('-'),
                TextColumn::make('regime.nome')
                    ->label('Regime')
                    ->placeholder('-'),
                TextColumn::make('data_entrada')
                    ->label('Entrada')
                    ->date('d/m/Y')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('categoria')
                    ->label('Categoria')
                    ->options(Residencia::CATEGORIAS),
                SelectFilter::make('apartamento')
                    ->label('Apartamento')
                    ->relationship('apartamento', 'numero'),
                SelectFilter::make('regime')
                    ->label('Regime')
                    ->relationship('regime', 'nome'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('aluno.nome');
    }
}