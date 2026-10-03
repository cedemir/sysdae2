<?php

namespace App\Filament\Resources\Cursos\Schemas;

use App\Models\Curso;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CursoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nome')
                    ->label('Nome'),
                TextEntry::make('sigla')
                    ->label('Sigla')
                    ->placeholder('-'),
                TextEntry::make('nivel')
                    ->label('Nível')
                    ->formatStateUsing(fn (?string $state) => Curso::NIVEIS[$state] ?? $state),
                TextEntry::make('duracao_anos')
                    ->label('Duração (anos)')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('ativo')
                    ->label('Ativo')
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
