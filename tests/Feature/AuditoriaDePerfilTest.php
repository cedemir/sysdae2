<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Auditoria;
use App\Support\Perfis;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditoriaDePerfilTest extends TestCase
{
    /** Registros de auditoria que falam de mudança de perfil. */
    private function registrosDePerfil()
    {
        return Auditoria::where('entidade', 'Usuário')->get()
            ->filter(fn (Auditoria $registro) => isset($registro->alteracoes['detalhes']['perfil']))
            ->values();
    }

    public function test_trocar_o_perfil_na_tela_registra_perfil_antigo_e_novo(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $usuario = $this->usuarioComPerfil(Perfis::RESIDENCIA);
        $novoPerfil = Role::findOrCreate(Perfis::PSICOSSOCIAL, 'web');

        Livewire::test(EditUser::class, ['record' => $usuario->getKey()])
            ->fillForm(['roles' => [$novoPerfil->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $registros = $this->registrosDePerfil();
        $this->assertCount(1, $registros);
        $this->assertSame('Residência Estudantil → Psicossocial', $registros->first()->alteracoes['detalhes']['perfil']);
        $this->assertSame($usuario->email, $registros->first()->descricao);
    }

    public function test_editar_sem_trocar_o_perfil_nao_registra_mudanca_de_perfil(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $usuario = $this->usuarioComPerfil(Perfis::RESIDENCIA);

        Livewire::test(EditUser::class, ['record' => $usuario->getKey()])
            ->fillForm(['name' => 'Outro Nome'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertCount(0, $this->registrosDePerfil());
    }

    public function test_criar_usuario_registra_o_perfil_inicial(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $perfil = Role::findOrCreate(Perfis::PSICOSSOCIAL, 'web');

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Nova Pessoa', 'email' => 'nova@exemplo.com', 'roles' => [$perfil->id],
                'ativo' => true, 'password' => 'Senha1234', 'password_confirmation' => 'Senha1234',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $registros = $this->registrosDePerfil();
        $this->assertCount(1, $registros);
        $this->assertSame('(nenhum) → Psicossocial', $registros->first()->alteracoes['detalhes']['perfil']);
    }

    public function test_comando_de_perfil_registra_perfil_antigo_e_novo(): void
    {
        $usuario = $this->usuarioComPerfil(Perfis::RESIDENCIA);
        Role::findOrCreate(Perfis::DAE_CENTRAL, 'web');

        $this->artisan('sysdae:perfil', ['email' => $usuario->email, 'perfil' => Perfis::DAE_CENTRAL])
            ->assertSuccessful();

        $registros = $this->registrosDePerfil();
        $this->assertCount(1, $registros);
        $this->assertSame('Residência Estudantil → DAE Central', $registros->first()->alteracoes['detalhes']['perfil']);
        $this->assertSame('sistema (console)', $registros->first()->user_nome);
    }
}