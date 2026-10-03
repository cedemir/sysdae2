<?php

namespace App\Filament\Resources\FichaSaudes\Schemas;

use App\Models\FichaSaude;
use App\Support\Visualizacao;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FichaSaudeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificação')
                ->description('Dados pessoais sensíveis: uso restrito à equipe autorizada.')
                ->columns(2)
                ->schema([
                    TextEntry::make('aluno_nome')->label('Aluno')->state(fn ($record) => $record->aluno?->nome),
                    TextEntry::make('tipo_sanguineo')->label('Tipo sanguíneo')
                        ->formatStateUsing(fn (?string $state) => FichaSaude::TIPOS_SANGUINEOS[$state] ?? $state),
                    TextEntry::make('cartao_sus')->label('Cartão SUS')->placeholder('-'),
                    TextEntry::make('plano_saude')->label('Plano de saúde')->placeholder('-'),
                    TextEntry::make('unidade_referencia')->label('Unidade de saúde de referência')
                        ->placeholder('-')->columnSpanFull(),
                ]),

            Section::make('Saúde e nutrição')->columns(2)->schema([
                Visualizacao::texto('alergias', 'Alergias'),
                Visualizacao::texto('restricoes_alimentares', 'Restrições alimentares'),
                Visualizacao::texto('condicoes_saude', 'Condições de saúde'),
                Visualizacao::texto('medicamentos_uso', 'Medicamentos de uso contínuo'),
                Visualizacao::texto('necessidades_especiais', 'Necessidades especiais'),
                Visualizacao::texto('observacoes', 'Observações'),
            ]),

            Section::make('Atualização')->columns(2)->schema([
                TextEntry::make('updated_at')->label('Atualizada em')->dateTime('d/m/Y H:i'),
                TextEntry::make('atualizada_por')->label('Atualizada por')->placeholder('-')
                    ->state(fn (FichaSaude $record) => $record->atualizadoPor?->name),
            ]),
        ]);
    }
}