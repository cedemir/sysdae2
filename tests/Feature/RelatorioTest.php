<?php

namespace Tests\Feature;

use App\Models\Falta;
use App\Support\Perfis;
use Tests\TestCase;

class RelatorioTest extends TestCase
{
    public function test_estatisticas_abrem_para_a_residencia(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));

        $this->get(route('relatorios.gerar', ['tipo' => 'estatisticas']))
            ->assertOk()
            ->assertSee('Estatísticas da residência');
    }

    public function test_perfil_sem_permissao_recebe_403(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::PSICOSSOCIAL));

        $this->get(route('relatorios.gerar', ['tipo' => 'faltas']))->assertForbidden();
    }

    public function test_relatorio_pode_ser_baixado_em_pdf(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));

        $this->get(route('relatorios.gerar', ['tipo' => 'estatisticas', 'formato' => 'pdf']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_filtro_de_periodo_nas_faltas(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));
        $aluno = $this->criarAluno();

        Falta::create(['aluno_id' => $aluno->id, 'data_falta' => '2026-03-10', 'observacao' => 'obs-antiga-xyz']);
        Falta::create(['aluno_id' => $aluno->id, 'data_falta' => '2026-03-20', 'observacao' => 'obs-recente-xyz']);

        $this->get(route('relatorios.gerar', ['tipo' => 'faltas', 'de' => '2026-03-15']))
            ->assertOk()
            ->assertSee('obs-recente-xyz')
            ->assertDontSee('obs-antiga-xyz');
    }

    public function test_filtro_por_aluno_nas_faltas(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));
        $a = $this->criarAluno();
        $b = $this->criarAluno();

        Falta::create(['aluno_id' => $a->id, 'data_falta' => '2026-03-10', 'observacao' => 'obs-do-aluno-a']);
        Falta::create(['aluno_id' => $b->id, 'data_falta' => '2026-03-10', 'observacao' => 'obs-do-aluno-b']);

        $this->get(route('relatorios.gerar', ['tipo' => 'faltas', 'cpf' => $a->cpf]))
            ->assertOk()
            ->assertSee('obs-do-aluno-a')
            ->assertDontSee('obs-do-aluno-b');
    }

    public function test_cpf_inexistente_volta_com_erro(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));

        $this->get(route('relatorios.gerar', ['tipo' => 'faltas', 'cpf' => '52998224725']))
            ->assertSessionHasErrors('cpf');
    }

    public function test_ficha_do_aluno_exige_o_cpf(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));

        $this->get(route('relatorios.gerar', ['tipo' => 'ficha']))->assertSessionHasErrors('cpf');
    }

    public function test_geracao_de_relatorio_e_auditada(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));

        $this->get(route('relatorios.gerar', ['tipo' => 'estatisticas']))->assertOk();

        $this->assertDatabaseHas('auditorias', ['evento' => 'gerou', 'entidade' => 'Relatório']);
    }
}