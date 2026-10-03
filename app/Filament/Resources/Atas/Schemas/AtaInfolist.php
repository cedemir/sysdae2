<?php

namespace App\Filament\Resources\Atas\Schemas;

use App\Support\Visualizacao;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ata de reunião')->columns(2)->schema([
                TextEntry::make('numero')->label('Nº da ata'),
                TextEntry::make('data_reuniao')->label('Data da reunião')->date('d/m/Y'),
                TextEntry::make('assunto')->label('Assunto')->columnSpanFull(),
                Visualizacao::texto('participantes', 'Participantes'),
                TextEntry::make('alunos_citados')->label('Alunos citados')->badge()->placeholder('Nenhum aluno citado')
                    ->state(fn ($record) => $record->alunos->pluck('nome')->all())
                    ->columnSpanFull(),
                Visualizacao::texto('pauta', 'Pauta'),
                Visualizacao::texto('deliberacoes', 'Deliberações'),
                Visualizacao::texto('encaminhamentos', 'Encaminhamentos'),
            ]),
        ]);
    }
}