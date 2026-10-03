<?php

namespace Tests\Feature;

use App\Models\Atendimento;
use App\Models\Aluno;
use App\Models\Auditoria;
use App\Models\Curso;
use App\Models\FichaSaude;
use App\Models\Ocorrencia;
use App\Models\Residencia;
use App\Models\TrocaApartamento;
use App\Models\User;
use App\Support\Perfis;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AcessoPorPerfilTest extends TestCase
{
    public function test_visitante_e_enviado_ao_login(): void
    {
        $this->get(route('relatorios.index'))->assertRedirect(route('login'));
    }

    public function test_usuario_sem_perfil_nao_acessa_nada(): void
    {
        $semPerfil = User::factory()->create();

        $this->assertFalse(Gate::forUser($semPerfil)->allows('viewAny', Aluno::class));
        $this->assertFalse(Gate::forUser($semPerfil)->allows('viewAny', Curso::class));
        $this->assertFalse($semPerfil->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_so_quem_tem_perfil_entra_no_painel(): void
    {
        $this->assertTrue($this->usuarioComPerfil(Perfis::RESIDENCIA)->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_cadastros_da_residencia(): void
    {
        $residencia = $this->usuarioComPerfil(Perfis::RESIDENCIA);
        $dae = $this->usuarioComPerfil(Perfis::DAE_CENTRAL);

        $this->assertTrue(Gate::forUser($residencia)->allows('create', Curso::class));
        $this->assertTrue(Gate::forUser($residencia)->allows('create', Residencia::class));

        // O DAE Central só consulta os alunos (modo somente leitura) e não mexe nos cadastros da residência.
        $this->assertTrue(Gate::forUser($dae)->allows('viewAny', Aluno::class));
        $this->assertFalse(Gate::forUser($dae)->allows('create', Aluno::class));
        $this->assertFalse(Gate::forUser($dae)->allows('create', Curso::class));
    }

    public function test_dados_sensiveis_por_perfil(): void
    {
        $psico = $this->usuarioComPerfil(Perfis::PSICOSSOCIAL);
        $dae = $this->usuarioComPerfil(Perfis::DAE_CENTRAL);
        $residencia = $this->usuarioComPerfil(Perfis::RESIDENCIA);

        $this->assertTrue(Gate::forUser($psico)->allows('create', Atendimento::class));
        $this->assertTrue(Gate::forUser($psico)->allows('create', FichaSaude::class));
        $this->assertFalse(Gate::forUser($dae)->allows('viewAny', Atendimento::class));
        $this->assertFalse(Gate::forUser($dae)->allows('viewAny', FichaSaude::class));
        $this->assertFalse(Gate::forUser($residencia)->allows('viewAny', Atendimento::class));

        $this->assertTrue(Gate::forUser($dae)->allows('create', Ocorrencia::class));
        $this->assertFalse(Gate::forUser($residencia)->allows('viewAny', Ocorrencia::class));
    }

    public function test_historico_e_auditoria_nao_sao_editaveis_nem_pelo_admin(): void
    {
        $admin = $this->usuarioComPerfil(Perfis::ADMIN);

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', TrocaApartamento::class));
        $this->assertFalse(Gate::forUser($admin)->allows('create', TrocaApartamento::class));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Auditoria::class));
        $this->assertFalse(Gate::forUser($admin)->allows('create', Auditoria::class));
    }

    public function test_auditoria_e_so_do_administrador(): void
    {
        foreach ([Perfis::RESIDENCIA, Perfis::DAE_CENTRAL, Perfis::PSICOSSOCIAL] as $perfil) {
            $this->assertFalse(
                Gate::forUser($this->usuarioComPerfil($perfil))->allows('viewAny', Auditoria::class),
                "O perfil {$perfil} não deveria ver a auditoria."
            );
        }
    }

    public function test_curso_com_turma_nao_pode_ser_excluido(): void
    {
        $admin = $this->usuarioComPerfil(Perfis::ADMIN);
        $curso = $this->criarCurso();

        $this->assertTrue(Gate::forUser($admin)->allows('delete', $curso));

        $this->criarTurma($curso);

        $this->assertFalse(Gate::forUser($admin)->allows('delete', $curso));
    }

    public function test_apartamento_com_morador_nao_pode_ser_excluido(): void
    {
        $admin = $this->usuarioComPerfil(Perfis::ADMIN);
        $apto = $this->criarApartamento();

        $this->assertTrue(Gate::forUser($admin)->allows('delete', $apto));

        Residencia::create(['aluno_id' => $this->criarAluno()->id, 'categoria' => 'residente', 'apartamento_id' => $apto->id]);

        $this->assertFalse(Gate::forUser($admin)->allows('delete', $apto));
    }
}