<?php

namespace App\Filament\Resources\Turmas\Schemas;

use App\Models\Turma;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TurmaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('codigo')
                    ->label('Código'),
                TextEntry::make('curso.nome')
                    ->label('Curso'),
                TextEntry::make('serie.nome')
                    ->label('Série')
                    ->placeholder('-'),
                TextEntry::make('ano_letivo')
                    ->label('Ano letivo'),
                TextEntry::make('turno')
                    ->label('Turno')
                    ->formatStateUsing(fn (?string $state) => Turma::TURNOS[$state] ?? $state),
                IconEntry::make('ativa')
                    ->label('Ativa')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
            ]);
    }
}
