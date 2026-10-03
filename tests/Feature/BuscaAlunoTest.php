<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Formatos;
use App\Support\Perfis;
use Tests\TestCase;

class BuscaAlunoTest extends TestCase
{
    private function buscar(string $termo)
    {
        return $this->getJson(route('relatorios.alunos', ['q' => $termo]));
    }

    public function test_busca_exige_login(): void
    {
        $this->buscar('ma')->assertUnauthorized();
    }

    public function test_usuario_sem_perfil_nao_busca(): void
    {
        $this->actingAs(User::factory()->create());

        $this->buscar('ma')->assertForbidden();
    }

    public function test_busca_por_parte_do_nome(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));
        $this->criarAluno(['nome' => 'Maria Souza']);
        $this->criarAluno(['nome' => 'Mariana Lima']);
        $this->criarAluno(['nome' => 'Joao Silva']);

        $resposta = $this->buscar('maria')->assertOk()->assertJsonCount(2);

        $this->assertSame(['Maria Souza', 'Mariana Lima'], array_column($resposta->json(), 'nome'));
    }

    public function test_busca_por_cpf_com_ou_sem_pontuacao(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::DAE_CENTRAL));
        $aluno = $this->criarAluno(['nome' => 'Pessoa Unica']);
        $this->criarAluno(['nome' => 'Outra Pessoa']);

        $this->buscar($aluno->cpf)->assertOk()->assertJsonCount(1)->assertJsonFragment(['nome' => 'Pessoa Unica']);
        $this->buscar(Formatos::cpf($aluno->cpf))->assertOk()->assertJsonCount(1);
    }

    public function test_resposta_traz_so_nome_e_cpf(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::PSICOSSOCIAL));
        $aluno = $this->criarAluno(['nome' => 'Fulano de Tal', 'observacoes' => 'reservado', 'email' => 'fulano@exemplo.com']);

        $resposta = $this->buscar('Fulano')->assertOk()->json();

        $this->assertSame(['nome', 'cpf', 'cpf_formatado'], array_keys($resposta[0]));
        $this->assertSame(Formatos::cpf($aluno->cpf), $resposta[0]['cpf_formatado']);
    }

    public function test_termo_curto_ou_so_de_curingas_nao_busca_nada(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));
        $this->criarAluno(['nome' => 'Alguem']);

        $this->buscar('a')->assertOk()->assertExactJson([]);
        $this->buscar('%%')->assertOk()->assertExactJson([]);
        $this->buscar('__')->assertOk()->assertExactJson([]);
    }

    public function test_no_maximo_dez_sugestoes(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));
        for ($i = 1; $i <= 12; $i++) {
            $this->criarAluno(['nome' => 'Teste Numero ' . $i]);
        }

        $this->buscar('Teste')->assertOk()->assertJsonCount(10);
    }

    public function test_tela_de_relatorios_tem_o_campo_de_busca(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));

        $this->get(route('relatorios.index'))->assertOk()->assertSee('Digite o nome ou o CPF');
    }
}