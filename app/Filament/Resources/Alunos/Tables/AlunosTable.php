<?php

namespace App\Filament\Resources\Alunos\Tables;

use App\Models\Aluno;
use App\Support\Formatos;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AlunosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cpf')
                    ->label('CPF')
                    ->formatStateUsing(fn (?string $state) => Formatos::cpf($state))
                    ->searchable(),
                TextColumn::make('matriculaAtual.turma.curso.nome')
                    ->label('Curso')
                    ->placeholder('Sem matrícula'),
                TextColumn::make('matriculaAtual.turma.codigo')
                    ->label('Turma')
                    ->placeholder('-'),
                TextColumn::make('residencia.apartamento.numero')
                    ->label('Apto')
                    ->placeholder('-'),
                TextColumn::make('situacao')
                    ->label('Situação')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Aluno::SITUACOES[$state] ?? $state)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('situacao')
                    ->label('Situação')
                    ->options(Aluno::SITUACOES),
                SelectFilter::make('sexo')
                    ->label('Sexo')
                    ->options(Aluno::SEXOS),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nome');
    }
}