<?php

namespace App\Filament\Resources\Pernoites\Schemas;

use App\Support\Visualizacao;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PernoiteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Autorização de pernoite')->columns(2)->schema([
                TextEntry::make('aluno_nome')->label('Aluno')->state(fn ($record) => $record->aluno?->nome),
                TextEntry::make('data_pernoite')->label('Data')->date('d/m/Y'),
                IconEntry::make('parcial')->label('Autorização parcial')->boolean(),
                TextEntry::make('forma_autorizacao')->label('Forma de autorização')->placeholder('-'),
                TextEntry::make('quem_autorizou')->label('Quem autorizou'),
                Visualizacao::texto('justificativa', 'Justificativa'),
            ]),
        ]);
    }
}