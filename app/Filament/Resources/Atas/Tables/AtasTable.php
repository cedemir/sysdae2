<?php

namespace App\Filament\Resources\Atas\Tables;

use App\Models\Ata;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AtasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('data_reuniao')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('assunto')
                    ->label('Assunto')
                    ->searchable()
                    ->limit(60),
                TextColumn::make('alunos_count')
                    ->label('Alunos')
                    ->counts('alunos'),
            ])
            ->filters([
                SelectFilter::make('alunos')
                    ->label('Aluno citado')
                    ->relationship('alunos', 'nome')
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('imprimir')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Ata $record): string => route('relatorios.ata', $record))
                    ->openUrlInNewTab(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('data_reuniao', 'desc');
    }
}