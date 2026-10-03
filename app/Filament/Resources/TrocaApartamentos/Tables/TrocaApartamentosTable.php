<?php

namespace App\Filament\Resources\TrocaApartamentos\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TrocaApartamentosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data_troca')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('aluno.nome')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('origem.numero')
                    ->label('Saiu do apartamento')
                    ->placeholder('-'),
                TextColumn::make('destino.numero')
                    ->label('Foi para')
                    ->placeholder('sem apartamento'),
                TextColumn::make('usuario.name')
                    ->label('Registrado por')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('aluno')
                    ->label('Aluno')
                    ->relationship('aluno', 'nome')
                    ->searchable(),
            ])
            ->recordUrl(null)
            ->defaultSort('data_troca', 'desc');
    }
}