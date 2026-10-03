<?php

namespace App\Filament\Resources\Atendimentos\Tables;

use App\Models\Atendimento;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AtendimentosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data_atendimento')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('hora_atendimento')
                    ->label('Hora')
                    ->time('H:i')
                    ->placeholder('-'),
                TextColumn::make('aluno.nome')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('forma')
                    ->label('Forma')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Atendimento::FORMAS[$state] ?? $state),
                TextColumn::make('servidores')
                    ->label('Servidores')
                    ->limit(40),
                IconColumn::make('sigiloso')
                    ->label('Sigiloso')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open'),
            ])
            ->filters([
                SelectFilter::make('aluno')
                    ->label('Aluno')
                    ->relationship('aluno', 'nome')
                    ->searchable(),
                SelectFilter::make('forma')
                    ->label('Forma')
                    ->options(Atendimento::FORMAS),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('data_atendimento', 'desc');
    }
}