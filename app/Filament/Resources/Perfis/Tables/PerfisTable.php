<?php

namespace App\Filament\Resources\Perfis\Tables;

use App\Models\Perfil;
use App\Support\Perfis;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PerfisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rotulo')
                    ->label('Nome')
                    ->formatStateUsing(fn (Perfil $record): string => $record->nome)
                    ->description(fn (Perfil $record): ?string => $record->descricao)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->state(fn (Perfil $record): string => $record->original() ? 'Original' : 'Cadastrado')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Original' ? 'gray' : 'primary'),
                TextColumn::make('name')
                    ->label('Código interno')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('users_count')
                    ->label('Usuários')
                    ->counts('users'),
                IconColumn::make('ve_sigilosos')
                    ->label('Vê sigilosos')
                    ->state(fn (Perfil $record): bool => Perfis::perfilVeSigilosos($record))
                    ->boolean(),
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
                EditAction::make(),
            ])
            ->defaultSort('id');
    }
}
