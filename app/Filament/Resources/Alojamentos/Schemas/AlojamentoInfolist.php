<?php

namespace App\Filament\Resources\Alojamentos\Schemas;

use App\Models\Alojamento;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AlojamentoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nome')
                    ->label('Nome'),
                TextEntry::make('localizacao')
                    ->label('Localização')
                    ->placeholder('-'),
                TextEntry::make('publico')
                    ->label('Público')
                    ->formatStateUsing(fn (?string $state) => Alojamento::PUBLICOS[$state] ?? $state),
                TextEntry::make('observacoes')
                    ->label('Observações')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
