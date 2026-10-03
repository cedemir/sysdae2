<?php

namespace App\Console\Commands;

use App\Support\PermissoesPerfil;
use Illuminate\Console\Command;

class SincronizarPermissoes extends Command
{
    protected $signature = 'sysdae:permissoes-sincronizar';

    protected $description = 'Cria as linhas que faltam na tabela de acessos por perfil (com o padrão de cada cadastro)';

    public function handle(): int
    {
        $criadas = PermissoesPerfil::sincronizar();

        $this->info($criadas === 0
            ? 'Nada a criar: a tabela de acessos já está completa.'
            : "{$criadas} linha(s) criada(s) com o padrão de cada cadastro.");

        return self::SUCCESS;
    }
}