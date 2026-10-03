<?php

namespace App\Filament\Resources\Apartamentos\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ApartamentoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('numero')
                    ->label('Número'),
                TextEntry::make('alojamento.nome')
                    ->label('Alojamento'),
                TextEntry::make('andar')
                    ->label('Andar')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('capacidade')
                    ->label('Capacidade')
                    ->numeric(),
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
