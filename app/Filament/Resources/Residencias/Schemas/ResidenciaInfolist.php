<?php

namespace App\Filament\Resources\Residencias\Schemas;

use App\Models\Residencia;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ResidenciaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('aluno.nome')
                    ->label('Aluno'),
                TextEntry::make('categoria')
                    ->label('Categoria')
                    ->formatStateUsing(fn (?string $state) => Residencia::CATEGORIAS[$state] ?? $state),
                TextEntry::make('apartamento.numero')
                    ->label('Apartamento')
                    ->formatStateUsing(fn ($state, $record) => $state . ' - ' . $record->apartamento?->alojamento?->nome)
                    ->placeholder('-'),
                TextEntry::make('regime.nome')
                    ->label('Regime')
                    ->placeholder('-'),
                TextEntry::make('data_entrada')
                    ->label('Data de entrada')
                    ->date('d/m/Y')
                    ->placeholder('-'),
                TextEntry::make('ocorrencias')
                    ->label('Ocorrências')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
