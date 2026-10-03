<?php

namespace Tests\Feature;

use App\Models\Ocorrencia;
use App\Support\Perfis;
use Tests\TestCase;

class SigiloTest extends TestCase
{
    private function ocorrencias(): array
    {
        $aluno = $this->criarAluno();

        $comum = Ocorrencia::create([
            'aluno_id' => $aluno->id,
            'data_ocorrencia' => '2026-03-10',
            'descricao' => 'Descricao comum visivel',
            'sigiloso' => false,
        ]);
        $sigilosa = Ocorrencia::create([
            'aluno_id' => $aluno->id,
            'data_ocorrencia' => '2026-03-11',
            'descricao' => 'Descricao sigilosa oculta',
            'sigiloso' => true,
        ]);

        return [$comum, $sigilosa];
    }

    public function test_dae_central_nao_ve_ocorrencias_sigilosas(): void
    {
        $this->ocorrencias();

        $this->actingAs($this->usuarioComPerfil(Perfis::DAE_CENTRAL));

        $this->assertSame(1, Ocorrencia::count());
    }

    public function test_administrador_e_psicossocial_veem_todas(): void
    {
        $this->ocorrencias();

        foreach ([Perfis::ADMIN, Perfis::PSICOSSOCIAL] as $perfil) {
            $this->actingAs($this->usuarioComPerfil($perfil));
            $this->assertSame(2, Ocorrencia::count(), "O perfil {$perfil} deveria ver as duas ocorrências.");
        }
    }

    public function test_sem_usuario_logado_o_sigilo_continua_valendo(): void
    {
        $this->ocorrencias();

        $this->assertSame(1, Ocorrencia::count());
    }

    public function test_anexos_de_ocorrencia_sigilosa_nao_sao_encontrados(): void
    {
        [$comum, $sigilosa] = $this->ocorrencias();

        $this->actingAs($this->usuarioComPerfil(Perfis::DAE_CENTRAL));

        $this->get(route('anexos.ocorrencia', $comum))->assertOk();
        $this->get(route('anexos.ocorrencia', $sigilosa))->assertNotFound();
    }

    public function test_relatorio_de_ocorrencias_respeita_o_sigilo(): void
    {
        $this->ocorrencias();

        $this->actingAs($this->usuarioComPerfil(Perfis::DAE_CENTRAL));
        $this->get(route('relatorios.gerar', ['tipo' => 'ocorrencias']))
            ->assertOk()
            ->assertSee('Descricao comum visivel')
            ->assertDontSee('Descricao sigilosa oculta');

        $this->actingAs($this->usuarioComPerfil(Perfis::PSICOSSOCIAL));
        $this->get(route('relatorios.gerar', ['tipo' => 'ocorrencias']))
            ->assertOk()
            ->assertSee('Descricao sigilosa oculta');
    }
}