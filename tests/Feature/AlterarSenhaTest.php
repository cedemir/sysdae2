<?php

namespace Tests\Feature;

use App\Filament\Pages\AlterarSenha;
use App\Filament\Pages\Painel;
use App\Models\Auditoria;
use App\Support\Perfis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AlterarSenhaTest extends TestCase
{
    public function test_usuario_troca_a_propria_senha(): void
    {
        $usuario = $this->entrarNoPainel(Perfis::RESIDENCIA);
        $usuario->forceFill(['password' => Hash::make('senhaAntiga1')])->save();

        DB::table('sessions')->insert([
            'id' => 'sessao-em-outro-computador', 'user_id' => $usuario->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'teste', 'payload' => '', 'last_activity' => time(),
        ]);

        Livewire::test(AlterarSenha::class)
            ->fillForm([
                'senha_atual' => 'senhaAntiga1',
                'nova_senha' => 'senhaNova123',
                'confirmacao' => 'senhaNova123',
            ])
            ->call('salvar')
            ->assertHasNoFormErrors()
            ->assertNotified('Senha alterada com sucesso.');

        $this->assertTrue(Hash::check('senhaNova123', $usuario->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'sessao-em-outro-computador']);
        $this->assertTrue(Auditoria::where('evento', 'alterou_senha')->where('registro_id', $usuario->id)->exists());
    }

    public function test_senha_atual_errada_ou_confirmacao_diferente_sao_recusadas(): void
    {
        $usuario = $this->entrarNoPainel(Perfis::RESIDENCIA);
        $usuario->forceFill(['password' => Hash::make('senhaAntiga1')])->save();

        Livewire::test(AlterarSenha::class)
            ->fillForm([
                'senha_atual' => 'senhaErrada9',
                'nova_senha' => 'senhaNova123',
                'confirmacao' => 'outraCoisa123',
            ])
            ->call('salvar')
            ->assertHasFormErrors(['senha_atual', 'confirmacao']);

        Livewire::test(AlterarSenha::class)
            ->fillForm([
                'senha_atual' => 'senhaAntiga1',
                'nova_senha' => 'curta',
                'confirmacao' => 'curta',
            ])
            ->call('salvar')
            ->assertHasFormErrors(['nova_senha']);

        $this->assertTrue(Hash::check('senhaAntiga1', $usuario->fresh()->password));
    }

    public function test_o_painel_tem_o_botao_e_a_pagina_abre_para_todos_os_perfis(): void
    {
        foreach ([Perfis::ADMIN, Perfis::DAE_CENTRAL, Perfis::RESIDENCIA, Perfis::PSICOSSOCIAL] as $perfil) {
            $this->entrarNoPainel($perfil);

            $this->get(Painel::getUrl())->assertOk()->assertSee(AlterarSenha::getUrl());
            $this->get(AlterarSenha::getUrl())->assertOk()->assertSee('Senha atual');
        }

        // Fica fora do menu lateral.
        $this->assertFalse(AlterarSenha::shouldRegisterNavigation());
    }
}
