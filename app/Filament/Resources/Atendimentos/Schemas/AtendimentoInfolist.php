<?php

namespace App\Filament\Resources\Atendimentos\Schemas;

use App\Models\Atendimento;
use App\Support\Visualizacao;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtendimentoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Atendimento')->columns(2)->schema([
                TextEntry::make('aluno_nome')->label('Aluno')->state(fn ($record) => $record->aluno?->nome),
                TextEntry::make('forma')->label('Forma de atendimento')->badge()
                    ->formatStateUsing(fn (?string $state) => Atendimento::FORMAS[$state] ?? $state),
                TextEntry::make('data_atendimento')->label('Data')->date('d/m/Y'),
                TextEntry::make('hora_atendimento')->label('Hora')->placeholder('-')
                    ->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 5) : null),
                TextEntry::make('servidores')->label('Servidores responsáveis')->columnSpanFull(),
                IconEntry::make('sigiloso')->label('Sigiloso')->boolean(),
                Visualizacao::texto('relato', 'Relato do atendimento'),
                Visualizacao::texto('outras_observacoes', 'Outras observações'),
            ]),

            Section::make('História de vida e encaminhamentos')->schema([
                Visualizacao::texto('historia_vida', 'História de vida'),
                Visualizacao::texto('encaminhamentos', 'Encaminhamentos'),
            ]),
        ]);
    }
}