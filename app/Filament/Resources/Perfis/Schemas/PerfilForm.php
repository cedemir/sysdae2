<?php

namespace App\Filament\Resources\Perfis\Schemas;

use App\Models\Perfil;
use App\Support\Perfis;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PerfilForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do perfil')
                    ->description('Depois de salvar, defina o que o perfil pode fazer no menu "Acessos por perfil".')
                    ->columns(2)
                    ->schema([
                        TextInput::make('rotulo')
                            ->label('Nome')
                            ->helperText('Exemplo: Assistência Social, Coordenação de Curso.')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Código interno')
                            ->helperText('Gerado a partir do nome. Não muda depois de criado.')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (string $operation): bool => $operation !== 'create'),
                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->rows(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('ativo')
                            ->label('Perfil ativo')
                            ->helperText('Os usuários que só têm um perfil inativo não conseguem entrar, e as sessões abertas são encerradas.')
                            ->default(true)
                            ->disabled(fn (?Perfil $record): bool => $record?->name === Perfis::ADMIN),
                        Toggle::make('ve_sigilosos')
                            ->label('Vê registros sigilosos')
                            ->helperText(fn (?Perfil $record): string => $record && in_array($record->name, Perfis::VEEM_SIGILOSOS, true)
                                ? 'Este perfil sempre vê os registros sigilosos.'
                                : 'Ocorrências e atendimentos marcados como sigilosos.')
                            ->default(false)
                            ->formatStateUsing(fn (?bool $state, ?Perfil $record): bool => (bool) $state || ($record && in_array($record->name, Perfis::VEEM_SIGILOSOS, true)))
                            ->disabled(fn (?Perfil $record): bool => $record && in_array($record->name, Perfis::VEEM_SIGILOSOS, true)),
                        Select::make('copiar_de')
                            ->label('Copiar os acessos de')
                            ->helperText('Opcional. Sem escolher, o perfil novo começa sem acesso a nada.')
                            ->options(fn (): array => array_diff_key(Perfis::rotulos(), [Perfis::ADMIN => true]))
                            ->dehydrated(false)
                            ->visible(fn (string $operation): bool => $operation === 'create'),
                    ]),
            ]);
    }
}
