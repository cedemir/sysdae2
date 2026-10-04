<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\AuditoriaDePerfil;
use App\Support\Perfis;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AtribuirPerfil extends Command
{
    protected $signature = 'sysdae:perfil {email : E-mail do usuário} {perfil : Código do perfil (admin, dae_central, residencia_estudantil, psicossocial, somente_consulta ou um perfil cadastrado)}';

    protected $description = 'Atribui um perfil de acesso a um usuário (substitui os perfis anteriores)';

    public function handle(): int
    {
        $perfil = (string) $this->argument('perfil');

        $validos = array_values(array_unique([...Perfis::TODOS, ...array_keys(Perfis::rotulos())]));

        if (! in_array($perfil, $validos, true)) {
            $this->error('Perfil inválido. Use: '.implode(', ', $validos));

            return self::FAILURE;
        }

        $usuario = User::where('email', $this->argument('email'))->first();
        if (! $usuario) {
            $this->error('Usuário não encontrado.');

            return self::FAILURE;
        }

        Role::findOrCreate($perfil, 'web');
        $antes = $usuario->getRoleNames()->all();
        $usuario->syncRoles([$perfil]);
        AuditoriaDePerfil::registrar($usuario, $antes, [$perfil]);

        $this->info("Perfil '{$perfil}' atribuído a {$usuario->email}.");

        return self::SUCCESS;
    }
}
