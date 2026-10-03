<?php

namespace Tests\Feature;

use App\Models\Acesso;
use App\Models\Atendimento;
use App\Models\Auditoria;
use App\Services\RelatorioService;
use App\Support\Perfis;
use App\Support\PermissoesPerfil;
use Tests\TestCase;

class RelatoriosPorAcessoTest extends TestCase
{
    private function permitidos(string $perfil): array
    {
        $chaves = array_keys(RelatorioService::permitidos($this->usuarioComPerfil($perfil)));
        sort($chaves);

        return $chaves;
    }

    private function linha(string $perfil, string $recurso): Acesso
    {
        return Acesso::where('perfil', $perfil)->where('recurso', $recurso)->firstOrFail();
    }

    public function test_cada_relatorio_ganha_uma_linha_por_perfil(): void
    {
        PermissoesPerfil::sincronizar();

        $relatorios = array_keys(RelatorioService::tipos());
        $linhas = Acesso::get()->filter(fn (Acesso $acesso) => str_starts_with($acesso->recurso, 'relatorio_'));

        $this->assertCount(count($relatorios) * 4, $linhas); // 4 perfis configuráveis
        $this->assertSame('Pode gerar', \App\Support\Recursos::niveisPermitidos('relatorio_faltas')['consulta']);
    }

    public function test_o_acesso_padrao_aos_relatorios_nao_mudou(): void
    {
        $esperado = [
            Perfis::RESIDENCIA => ['apartamento', 'estatisticas', 'faltas', 'ficha', 'pernoites', 'trocas'],
            Perfis::DAE_CENTRAL => ['apartamento', 'estatisticas', 'faltas', 'ficha', 'ocorrencias', 'pernoites', 'trocas'],
            Perfis::PSICOSSOCIAL => ['atendimentos', 'ficha', 'ocorrencias'],
            Perfis::SOMENTE_CONSULTA => ['apartamento', 'estatisticas', 'ficha'],
        ];

        foreach ([false, true] as $comLinhasNaTabela) {
            if ($comLinhasNaTabela) {
                PermissoesPerfil::sincronizar();
            }
            foreach ($esperado as $perfil => $relatorios) {
                $this->assertSame($relatorios, $this->permitidos($perfil), "Perfil {$perfil} (com linhas na tabela: " . ($comLinhasNaTabela ? 'sim' : 'não') . ')');
            }
        }

        $todos = array_keys(RelatorioService::tipos());
        sort($todos);
        $this->assertSame($todos, $this->permitidos(Perfis::ADMIN));
    }

    public function test_tirar_a_linha_do_relatorio_tira_so_aquele_relatorio(): void
    {
        PermissoesPerfil::sincronizar();
        $this->linha(Perfis::RESIDENCIA, 'relatorio_faltas')->update(['nivel' => 'nenhum']);

        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));

        $this->get(route('relatorios.gerar', ['tipo' => 'faltas']))->assertForbidden();
        $this->get(route('relatorios.gerar', ['tipo' => 'estatisticas']))->assertOk();
        $this->get(route('relatorios.index'))
            ->assertOk()
            ->assertDontSee('Faltas na residência')
            ->assertSee('Trocas de apartamento');
    }

    public function test_tirar_o_acesso_a_tabela_tira_o_relatorio_dela(): void
    {
        PermissoesPerfil::sincronizar();
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));

        $this->get(route('relatorios.gerar', ['tipo' => 'faltas']))->assertOk();

        // A linha do relatório continua liberada; o que mudou foi o acesso à tabela de faltas.
        $this->linha(Perfis::RESIDENCIA, 'falta')->update(['nivel' => 'nenhum']);

        $this->get(route('relatorios.gerar', ['tipo' => 'faltas']))->assertForbidden();
    }

    public function test_liberar_so_o_relatorio_nao_basta_para_dado_sensivel(): void
    {
        PermissoesPerfil::sincronizar();
        $aluno = $this->criarAluno();
        Atendimento::create(['aluno_id' => $aluno->id, 'data_atendimento' => '2026-03-10', 'servidores' => 'Equipe',
            'forma' => 'presencial', 'relato' => 'visivel-xyz', 'sigiloso' => false]);
        Atendimento::create(['aluno_id' => $aluno->id, 'data_atendimento' => '2026-03-11', 'servidores' => 'Equipe',
            'forma' => 'presencial', 'relato' => 'oculto-xyz', 'sigiloso' => true]);

        $this->actingAs($this->usuarioComPerfil(Perfis::DAE_CENTRAL));

        // Só a linha do relatório: o DAE Central continua sem acesso à tabela de atendimentos.
        $this->linha(Perfis::DAE_CENTRAL, 'relatorio_atendimentos')->update(['nivel' => 'consulta']);
        $this->get(route('relatorios.gerar', ['tipo' => 'atendimentos']))->assertForbidden();

        // Com a tabela também liberada, o relatório abre, mas o sigilo continua valendo.
        $this->linha(Perfis::DAE_CENTRAL, 'atendimento')->update(['nivel' => 'consulta']);
        $this->get(route('relatorios.gerar', ['tipo' => 'atendimentos']))
            ->assertOk()
            ->assertSee('visivel-xyz')
            ->assertDontSee('oculto-xyz');
    }

    public function test_o_administrador_sempre_pode_gerar_todos(): void
    {
        PermissoesPerfil::sincronizar();
        Acesso::query()->get()->each->update(['nivel' => 'nenhum']);

        $todos = array_keys(RelatorioService::tipos());
        sort($todos);

        $this->assertSame($todos, $this->permitidos(Perfis::ADMIN));
    }

    public function test_perfil_sem_nenhum_relatorio_nao_ve_o_item_no_menu_nem_a_busca(): void
    {
        PermissoesPerfil::sincronizar();
        $psico = $this->usuarioComPerfil(Perfis::PSICOSSOCIAL);
        $this->actingAs($psico);

        $this->get('/admin')->assertOk()->assertSee('/relatorios');

        Acesso::where('perfil', Perfis::PSICOSSOCIAL)->get()
            ->filter(fn (Acesso $acesso) => str_starts_with($acesso->recurso, 'relatorio_'))
            ->each->update(['nivel' => 'nenhum']);

        $this->assertSame([], $this->permitidos(Perfis::PSICOSSOCIAL));
        $this->get('/admin')->assertOk()->assertDontSee('/relatorios');
        $this->get(route('relatorios.index'))->assertForbidden();
        $this->getJson(route('relatorios.alunos', ['q' => 'ab']))->assertForbidden();
    }

    public function test_mudanca_em_relatorio_entra_na_auditoria(): void
    {
        PermissoesPerfil::sincronizar();
        $this->actingAs($this->usuarioComPerfil(Perfis::ADMIN));

        $this->linha(Perfis::RESIDENCIA, 'relatorio_faltas')->update(['nivel' => 'nenhum']);

        $registro = Auditoria::where('entidade', 'Permissão de perfil')->where('evento', 'alterado')->latest('id')->firstOrFail();
        $this->assertSame('consulta', $registro->alteracoes['mudancas']['nivel']['de']);
        $this->assertSame('nenhum', $registro->alteracoes['mudancas']['nivel']['para']);
        $this->assertStringContainsString('Relatório: Faltas na residência', $registro->descricao);
    }
}