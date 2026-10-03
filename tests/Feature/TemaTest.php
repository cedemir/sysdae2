<?php

namespace Tests\Feature;

use App\Support\Perfis;
use App\Support\Tema;
use Tests\TestCase;

class TemaTest extends TestCase
{
    public function test_o_tema_padrao_e_o_classico(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('css/sysdae-tema.css', false)
            ->assertDontSee('sysdae-tema-moderno.css', false)
            ->assertSee('fi-topbar', false)
            ->assertSee('Usar tema moderno');
    }

    public function test_o_usuario_troca_para_o_tema_moderno_e_volta(): void
    {
        $usuario = $this->entrarNoPainel(Perfis::RESIDENCIA);

        $this->from('/admin/alunos')->get(route('tema.trocar', Tema::MODERNO))->assertRedirect('/admin/alunos');
        $this->assertSame(Tema::MODERNO, $usuario->fresh()->tema);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('sysdae-tema-moderno.css', false)
            ->assertSee('sd-marca-sigla', false)
            ->assertDontSee('class="fi-topbar', false)
            ->assertSee('Usar tema clássico');

        $this->get(route('tema.trocar', Tema::CLASSICO));
        $this->assertSame(Tema::CLASSICO, $usuario->fresh()->tema);
    }

    public function test_tema_invalido_e_visitante_sao_recusados(): void
    {
        $this->get(route('tema.trocar', Tema::MODERNO))->assertRedirect(route('login'));

        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $this->get('/tema/rosa')->assertNotFound();
    }
}
