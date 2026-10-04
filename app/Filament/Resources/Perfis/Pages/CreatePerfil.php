<?php

namespace App\Filament\Resources\Perfis\Pages;

use App\Filament\Resources\Perfis\PerfilResource;
use App\Support\PermissoesPerfil;
use Filament\Resources\Pages\CreateRecord;

class CreatePerfil extends CreateRecord
{
    protected static string $resource = PerfilResource::class;

    /** Cria as linhas do perfil na tela de acessos (copiando de outro perfil, se foi escolhido). */
    protected function afterCreate(): void
    {
        PermissoesPerfil::sincronizar();

        $origem = $this->data['copiar_de'] ?? null;
        if (filled($origem)) {
            PermissoesPerfil::copiar((string) $origem, $this->getRecord()->name);
        }
    }
}
