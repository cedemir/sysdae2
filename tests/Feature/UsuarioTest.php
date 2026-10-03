<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use App\Support\Perfis;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    private function perfilId(string $perfil): int
    {
        return Role::findOrCreate($perfil, 'web')->id;
    }

    public function test_usuario_inativo_nao_entra_no_painel(): void
    {
        $usuario = $this->usuarioComPerfil(Perfis::RESIDENCIA);
        $painel = Filament::getPanel('admin');

        $this->assertTrue($usuario->canAccessPanel($painel));

        $usuario->update(['ativo' => false]);

        $this->assertFalse($usuario->fresh()->canAccessPanel($painel));
    }

    public function test_inativar_encerra_as_sessoes_abertas(): void
    {
        $usuario = $this->usuarioComPerfil(Perfis::RESIDENCIA);

        DB::table('sessions')->insert([
            'id' => 'sessao-de-teste', 'user_id' => $usuario->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'teste', 'payload' => 'x', 'last_activity' => time(),
        ]);

        $usuario->update(['ativo' => false]);

        $this->assertDatabaseMissing('sessions', ['id' => 'sessao-de-teste']);
    }

    public function test_so_o_administrador_gerencia_usuarios(): void
    {
        $this->assertTrue(Gate::forUser($this->usuarioComPerfil(Perfis::ADMIN))->allows('viewAny', User::class));

        foreach ([Perfis::RESIDENCIA, Perfis::DAE_CENTRAL, Perfis::PSICOSSOCIAL] as $perfil) {
            $this->assertFalse(Gate::forUser($this->usuarioComPerfil($perfil))->allows('viewAny', User::class));
        }
    }

    public function test_ninguem_exclui_o_proprio_usuario_mas_pode_excluir_outro(): void
    {
        $admin = $this->usuarioComPerfil(Perfis::ADMIN);
        $outro = $this->usuarioComPerfil(Perfis::RESIDENCIA);

        $this->assertFalse(Gate::forUser($admin)->allows('delete', $admin));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $outro));
    }

    public function test_criar_usuario_com_perfil_e_senha_forte(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Maria Servidora', 'email' => 'maria@exemplo.com', 'roles' => [$this->perfilId(Perfis::PSICOSSOCIAL)],
                'ativo' => true, 'password' => 'Senha1234', 'password_confirmation' => 'Senha1234',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $criado = User::where('email', 'maria@exemplo.com')->firstOrFail();
        $this->assertTrue($criado->hasRole(Perfis::PSICOSSOCIAL));
        $this->assertTrue($criado->ativo);
        $this->assertNotSame('Senha1234', $criado->password);
    }

    public function test_senha_fraca_e_recusada(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);

        foreach (['12345678', 'abcdefgh', 'Ab1'] as $senha) {
            Livewire::test(CreateUser::class)
                ->fillForm([
                    'name' => 'Fulano', 'email' => 'fulano@exemplo.com', 'roles' => [$this->perfilId(Perfis::RESIDENCIA)],
                    'password' => $senha, 'password_confirmation' => $senha,
                ])
                ->call('create')
                ->assertHasFormErrors(['password']);
        }

        $this->assertDatabaseMissing('users', ['email' => 'fulano@exemplo.com']);
    }

    public function test_confirmacao_de_senha_precisa_conferir(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Fulano', 'email' => 'fulano@exemplo.com', 'roles' => [$this->perfilId(Perfis::RESIDENCIA)],
                'password' => 'Senha1234', 'password_confirmation' => 'Outra1234',
            ])
            ->call('create')
            ->assertHasFormErrors(['password_confirmation']);
    }

    public function test_novo_usuario_precisa_de_senha(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);

        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Fulano', 'email' => 'fulano@exemplo.com', 'roles' => [$this->perfilId(Perfis::RESIDENCIA)]])
            ->call('create')
            ->assertHasFormErrors(['password' => 'required']);
    }

    public function test_administrador_nao_inativa_a_si_mesmo(): void
    {
        $admin = $this->entrarNoPainel(Perfis::ADMIN);

        Livewire::test(EditUser::class, ['record' => $admin->getKey()])
            ->fillForm(['ativo' => false])
            ->call('save')
            ->assertHasFormErrors(['ativo']);

        $this->assertTrue($admin->fresh()->ativo);
    }

    public function test_o_unico_administrador_nao_perde_o_perfil(): void
    {
        $admin = $this->entrarNoPainel(Perfis::ADMIN);

        Livewire::test(EditUser::class, ['record' => $admin->getKey()])
            ->fillForm(['roles' => [$this->perfilId(Perfis::RESIDENCIA)]])
            ->call('save')
            ->assertHasFormErrors(['roles']);

        $this->assertTrue($admin->fresh()->hasRole(Perfis::ADMIN));
    }

    public function test_editar_sem_trocar_a_senha_mantem_a_senha(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $usuario = $this->usuarioComPerfil(Perfis::RESIDENCIA);
        $senhaAntes = $usuario->password;

        Livewire::test(EditUser::class, ['record' => $usuario->getKey()])
            ->fillForm(['name' => 'Nome Atualizado', 'password' => '', 'password_confirmation' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Nome Atualizado', $usuario->fresh()->name);
        $this->assertSame($senhaAntes, $usuario->fresh()->password);
    }
}