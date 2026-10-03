<?php

namespace App\Filament\Resources\FichaSaudes\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FichaSaudesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('aluno.nome')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo_sanguineo')
                    ->label('Tipo sang.')
                    ->formatStateUsing(fn (?string $state) => $state === 'nao_informado' ? '-' : $state),
                TextColumn::make('alergias')
                    ->label('Alergias')
                    ->limit(40)
                    ->placeholder('-'),
                TextColumn::make('restricoes_alimentares')
                    ->label('Restrições alimentares')
                    ->limit(40)
                    ->placeholder('-'),
                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('atualizadoPor.name')
                    ->label('Por')
                    ->placeholder('-'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('aluno.nome');
    }
}