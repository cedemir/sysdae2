<?php

namespace App\Filament\Resources\Ocorrencias\Tables;

use App\Models\Ocorrencia;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OcorrenciasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data_ocorrencia')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('aluno.nome')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(60),
                IconColumn::make('sigiloso')
                    ->label('Sigilosa')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open'),
                TextColumn::make('advertencia')
                    ->label('Advertência')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Ocorrencia::SITUACOES[$state] ?? $state)
                    ->placeholder('-'),
                IconColumn::make('suspensao_residencia')
                    ->label('Suspensão')
                    ->boolean(),
                TextColumn::make('perda_vaga')
                    ->label('Perda da vaga')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Ocorrencia::SITUACOES[$state] ?? $state)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('aluno')
                    ->label('Aluno')
                    ->relationship('aluno', 'nome')
                    ->searchable(),
                TernaryFilter::make('sigiloso')
                    ->label('Sigilo')
                    ->trueLabel('Somente sigilosas')
                    ->falseLabel('Somente não sigilosas'),
                SelectFilter::make('advertencia')
                    ->label('Advertência')
                    ->options(Ocorrencia::SITUACOES),
            ])
            ->recordActions([
                Action::make('anexos')
                    ->label(fn (Ocorrencia $record): string => 'Anexos (' . count($record->anexos ?? []) . ')')
                    ->icon('heroicon-o-paper-clip')
                    ->url(fn (Ocorrencia $record): string => route('anexos.ocorrencia', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Ocorrencia $record): bool => filled($record->anexos)),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('data_ocorrencia', 'desc');
    }
}