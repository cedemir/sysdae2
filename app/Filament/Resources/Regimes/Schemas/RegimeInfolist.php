<?php

namespace App\Filament\Resources\Regimes\Schemas;

use App\Models\Regime;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RegimeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nome')
                    ->label('Nome'),
                TextEntry::make('descricao')
                    ->label('Descrição')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('aplica_se_a')
                    ->label('Aplica-se a')
                    ->formatStateUsing(fn (?string $state) => Regime::APLICACOES[$state] ?? $state),
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
