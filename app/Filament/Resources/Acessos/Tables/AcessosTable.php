<?php

namespace App\Filament\Resources\Acessos\Tables;

use App\Models\Acesso;
use App\Support\Perfis;
use App\Support\PermissoesPerfil;
use App\Support\Recursos;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class AcessosTable
{
    public static function configure(Table $table): Table
    {
        // Garante uma linha para cada perfil e cadastro (útil quando surgir um cadastro novo).
        PermissoesPerfil::sincronizar();

        $perfis = collect(Recursos::perfisEditaveis())
            ->mapWithKeys(fn (string $perfil) => [$perfil => Perfis::rotulo($perfil)])
            ->all();

        return $table
            ->description('Escolha, para cada perfil, o que ele pode fazer em cada cadastro. '
                .'A mudança vale na hora. O administrador sempre tem acesso a tudo. '
                .'Ocorrências e atendimentos sigilosos só aparecem para o administrador, a equipe psicossocial '
                .'e os perfis marcados com "Vê registros sigilosos" no menu Perfis, mesmo que outro perfil tenha acesso ao cadastro. '
                .'Os relatórios aparecem no fim da lista de cada perfil e só funcionam para quem também '
                .'pode consultar as tabelas de onde eles leem os dados.')
            ->columns([
                TextColumn::make('recurso')
                    ->label('Cadastro')
                    ->formatStateUsing(fn (?string $state) => Recursos::rotulo($state))
                    ->description(fn (Acesso $record): string => Recursos::descricao($record->recurso)),
                SelectColumn::make('nivel')
                    ->label('Acesso')
                    ->options(fn (Acesso $record) => $record->niveisPermitidos())
                    ->selectablePlaceholder(false)
                    ->rules(fn (Acesso $record): array => [Rule::in(array_keys($record->niveisPermitidos()))]),
            ])
            ->groups([
                Group::make('perfil')
                    ->label('Perfil')
                    ->getTitleFromRecordUsing(fn (Acesso $record): string => Perfis::rotulo($record->perfil)),
            ])
            ->defaultGroup('perfil')
            ->filters([
                SelectFilter::make('perfil')
                    ->label('Perfil')
                    ->options($perfis),
                SelectFilter::make('nivel')
                    ->label('Acesso')
                    ->options(PermissoesPerfil::NIVEIS),
            ])
            ->recordUrl(null)
            ->paginated(false)
            ->defaultSort('ordem');
    }
}
