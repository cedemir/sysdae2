<?php

namespace App\Filament\Resources\Auditorias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuditoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->label('Usuário')
                    ->numeric(),
                TextInput::make('user_nome')
                    ->label('Nome do usuário')
                    ->required(),
                TextInput::make('evento')
                    ->label('Evento')
                    ->required(),
                TextInput::make('entidade')
                    ->label('Entidade')
                    ->required(),
                TextInput::make('registro_id')
                    ->label('Registro')
                    ->numeric(),
                TextInput::make('descricao')
                    ->label('Descrição'),
                TextInput::make('alteracoes')
                    ->label('Alterações'),
                TextInput::make('ip')
                    ->label('IP'),
            ]);
    }
}
