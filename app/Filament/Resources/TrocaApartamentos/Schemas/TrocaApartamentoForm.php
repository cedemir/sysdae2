<?php

namespace App\Filament\Resources\TrocaApartamentos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TrocaApartamentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('aluno_id')
                    ->label('Aluno')
                    ->relationship('aluno', 'id')
                    ->required(),
                TextInput::make('origem_apartamento_id')
                    ->label('Apartamento de origem')
                    ->numeric(),
                TextInput::make('destino_apartamento_id')
                    ->label('Apartamento de destino')
                    ->numeric(),
                DatePicker::make('data_troca')
                    ->label('Data da troca')
                    ->required(),
                TextInput::make('user_id')
                    ->label('Usuário')
                    ->numeric(),
            ]);
    }
}
