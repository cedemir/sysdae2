<?php

namespace App\Filament\Resources\Auditorias\Tables;

use App\Models\Auditoria;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditoriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Quando')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('user_nome')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('evento')
                    ->label('Evento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Auditoria::EVENTOS[$state] ?? $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'criado' => 'success',
                        'alterado' => 'warning',
                        'excluido', 'falhou' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('entidade')
                    ->label('O quê')
                    ->searchable(),
                TextColumn::make('descricao')
                    ->label('Registro')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('resumo')
                    ->label('Detalhes')
                    ->state(fn (Auditoria $record): string => $record->resumo())
                    ->limit(80)
                    ->tooltip(fn (Auditoria $record): string => $record->resumo())
                    ->wrap(),
                TextColumn::make('ip')
                    ->label('IP')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('evento')
                    ->label('Evento')
                    ->options(Auditoria::EVENTOS),
                SelectFilter::make('entidade')
                    ->label('O quê')
                    ->options(fn () => Auditoria::query()->distinct()->orderBy('entidade')->pluck('entidade', 'entidade')->all()),
            ])
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc');
    }
}