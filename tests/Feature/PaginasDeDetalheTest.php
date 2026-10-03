<?php

namespace Tests\Feature;

use App\Filament\Resources\Alojamentos\AlojamentoResource;
use App\Filament\Resources\Alunos\AlunoResource;
use App\Filament\Resources\Apartamentos\ApartamentoResource;
use App\Filament\Resources\Atas\AtaResource;
use App\Filament\Resources\Atendimentos\AtendimentoResource;
use App\Filament\Resources\Cursos\CursoResource;
use App\Filament\Resources\Faltas\FaltaResource;
use App\Filament\Resources\FichaSaudes\FichaSaudeResource;
use App\Filament\Resources\Matriculas\MatriculaResource;
use App\Filament\Resources\Ocorrencias\OcorrenciaResource;
use App\Filament\Resources\Pernoites\PernoiteResource;
use App\Filament\Resources\Regimes\RegimeResource;
use App\Filament\Resources\Residencias\ResidenciaResource;
use App\Filament\Resources\Series\SerieResource;
use App\Filament\Resources\Turmas\TurmaResource;
use App\Models\Alojamento;
use App\Models\Ata;
use App\Models\Atendimento;
use App\Models\Auditoria;
use App\Models\Falta;
use App\Models\FichaSaude;
use App\Models\Matricula;
use App\Models\Ocorrencia;
use App\Models\Pernoite;
use App\Models\Regime;
use App\Models\Residencia;
use App\Models\Serie;
use App\Support\Formatos;
use App\Support\Perfis;
use App\Support\PermissoesPerfil;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaginasDeDetalheTest extends TestCase
{
    public function test_o_administrador_abre_a_pagina_de_detalhe_de_todos_os_cadastros(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);

        $aluno = $this->criarAluno(['nome' => 'Aluno Residente Visivel']);
        $curso = $this->criarCurso(['nome' => 'Curso Visivel Abc']);
        $serie = Serie::create(['ordem' => 1, 'nome' => 'Serie Visivel', 'ativa' => true]);
        $turma = $this->criarTurma($curso, ['codigo' => 'TURMA-VISIVEL', 'serie_id' => $serie->id]);
        $alojamento = Alojamento::create(['nome' => 'Bloco Visivel', 'publico' => 'misto', 'ativo' => true]);
        $apto = $this->criarApartamento($alojamento, 4, ['numero' => 'AP9001']);
        $regime = Regime::create(['nome' => 'Regime Visivel', 'aplica_se_a' => 'todos', 'ativo' => true]);
        $matricula = Matricula::create([
            'aluno_id' => $aluno->id, 'turma_id' => $turma->id, 'numero' => 'MAT-VISIVEL',
            'data_matricula' => '2026-02-01', 'situacao' => 'cursando',
        ]);
        $residencia = Residencia::create([
            'aluno_id' => $aluno->id, 'categoria' => 'residente', 'apartamento_id' => $apto->id, 'regime_id' => $regime->id,
        ]);

        // [recurso, registro, textos que a página precisa mostrar (inclusive os que vêm de outras tabelas)]
        $casos = [
            [CursoResource::class, $curso, ['Curso Visivel Abc']],
            [SerieResource::class, $serie, ['Serie Visivel']],
            [TurmaResource::class, $turma, ['TURMA-VISIVEL', 'Curso Visivel Abc', 'Serie Visivel']],
            [AlojamentoResource::class, $alojamento, ['Bloco Visivel']],
            [ApartamentoResource::class, $apto, ['AP9001', 'Bloco Visivel']],
            [RegimeResource::class, $regime, ['Regime Visivel']],
            [MatriculaResource::class, $matricula, [
                'MAT-VISIVEL', 'Aluno Residente Visivel', Formatos::cpf($aluno->cpf), 'TURMA-VISIVEL', 'Curso Visivel Abc',
            ]],
            [ResidenciaResource::class, $residencia, ['Aluno Residente Visivel', 'AP9001', 'Bloco Visivel', 'Regime Visivel']],
            [FaltaResource::class, Falta::create([
                'aluno_id' => $aluno->id, 'data_falta' => '2026-03-10', 'observacao' => 'Obs falta visivel',
            ]), ['Obs falta visivel', 'Aluno Residente Visivel']],
            [PernoiteResource::class, Pernoite::create([
                'aluno_id' => $aluno->id, 'data_pernoite' => '2026-03-10', 'justificativa' => 'Justificativa visivel',
                'quem_autorizou' => 'Responsavel',
            ]), ['Justificativa visivel', 'Aluno Residente Visivel']],
            [OcorrenciaResource::class, Ocorrencia::create([
                'aluno_id' => $aluno->id, 'data_ocorrencia' => '2026-03-10', 'descricao' => 'Descricao visivel de ocorrencia',
            ]), ['Descricao visivel de ocorrencia', 'Aluno Residente Visivel']],
            [AtendimentoResource::class, Atendimento::create([
                'aluno_id' => $aluno->id, 'data_atendimento' => '2026-03-10', 'servidores' => 'Equipe',
                'forma' => 'presencial', 'relato' => 'Relato visivel de atendimento',
            ]), ['Relato visivel de atendimento', 'Aluno Residente Visivel']],
            [FichaSaudeResource::class, FichaSaude::create(['aluno_id' => $aluno->id, 'alergias' => 'Alergia visivel']), [
                'Alergia visivel', 'Aluno Residente Visivel',
            ]],
        ];

        foreach ($casos as [$recurso, $registro, $textos]) {
            $resposta = $this->get($recurso::getUrl('view', ['record' => $registro]))->assertOk();

            foreach ($textos as $texto) {
                $resposta->assertSee($texto);
            }
        }

        $ata = Ata::create(['numero' => '001/2026', 'data_reuniao' => '2026-03-10', 'assunto' => 'Assunto visivel da ata']);
        $ata->alunos()->attach($aluno->id);
        $this->get(AtaResource::getUrl('view', ['record' => $ata]))
            ->assertOk()
            ->assertSee('Assunto visivel da ata')
            ->assertSee('Aluno Residente Visivel');
    }

    public function test_a_pagina_de_detalhe_do_aluno_mostra_nomes_legiveis_e_os_dados_atuais(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $aluno = $this->criarAluno(['nome' => 'Aluno Legivel', 'sexo' => 'nao_dizer']);
        $turma = $this->criarTurma($this->criarCurso(['nome' => 'Curso do Aluno Legivel']), ['codigo' => 'TURMA-ALUNO']);
        Matricula::create([
            'aluno_id' => $aluno->id, 'turma_id' => $turma->id, 'numero' => 'MAT-ALUNO',
            'data_matricula' => '2026-02-01', 'situacao' => 'cursando',
        ]);
        Residencia::create([
            'aluno_id' => $aluno->id, 'categoria' => 'residente',
            'apartamento_id' => $this->criarApartamento(null, 4, ['numero' => 'AP7001'])->id,
        ]);

        $this->get(AlunoResource::getUrl('view', ['record' => $aluno]))
            ->assertOk()
            ->assertSee('Aluno Legivel')
            ->assertSee('Prefiro não dizer')
            ->assertSee('Cursando')
            ->assertSee('Curso do Aluno Legivel')
            ->assertSee('TURMA-ALUNO')
            ->assertSee('MAT-ALUNO')
            ->assertSee('AP7001');
    }

    public function test_quem_so_consulta_abre_o_detalhe_mas_nao_edita(): void
    {
        PermissoesPerfil::sincronizar();
        $this->entrarNoPainel(Perfis::SOMENTE_CONSULTA);
        $curso = $this->criarCurso(['nome' => 'Curso So Consulta']);

        $this->get(CursoResource::getUrl('view', ['record' => $curso]))->assertOk()->assertSee('Curso So Consulta');
        $this->get(CursoResource::getUrl('edit', ['record' => $curso]))->assertForbidden();
    }

    public function test_quem_nao_tem_acesso_ao_cadastro_nao_abre_o_detalhe(): void
    {
        $this->entrarNoPainel(Perfis::DAE_CENTRAL);
        $curso = $this->criarCurso();

        $this->get(CursoResource::getUrl('view', ['record' => $curso]))->assertForbidden();
    }

    public function test_ocorrencia_sigilosa_nao_abre_para_quem_nao_pode_ve_la(): void
    {
        $this->entrarNoPainel(Perfis::DAE_CENTRAL);
        $aluno = $this->criarAluno();
        $comum = Ocorrencia::create(['aluno_id' => $aluno->id, 'data_ocorrencia' => '2026-03-10', 'descricao' => 'comum', 'sigiloso' => false]);
        $sigilosa = Ocorrencia::create(['aluno_id' => $aluno->id, 'data_ocorrencia' => '2026-03-11', 'descricao' => 'sigilosa', 'sigiloso' => true]);

        $this->get(OcorrenciaResource::getUrl('view', ['record' => $comum]))->assertOk();
        $this->get(OcorrenciaResource::getUrl('view', ['record' => $sigilosa]))->assertNotFound();
    }

    public function test_abrir_o_detalhe_de_dado_sensivel_entra_na_auditoria(): void
    {
        $this->entrarNoPainel(Perfis::PSICOSSOCIAL);
        $aluno = $this->criarAluno(['nome' => 'Aluno Auditado']);
        $ficha = FichaSaude::create(['aluno_id' => $aluno->id, 'alergias' => 'x']);
        $atendimento = Atendimento::create([
            'aluno_id' => $aluno->id, 'data_atendimento' => '2026-03-10', 'servidores' => 'Equipe',
            'forma' => 'presencial', 'relato' => 'y',
        ]);
        DB::table('auditorias')->delete();

        $this->get(FichaSaudeResource::getUrl('view', ['record' => $ficha]))->assertOk();
        $this->get(AtendimentoResource::getUrl('view', ['record' => $atendimento]))->assertOk();

        $registros = Auditoria::where('evento', 'consultou')->orderBy('id')->get();
        $this->assertSame(['Ficha de saúde', 'Atendimento psicossocial'], $registros->pluck('entidade')->all());
        $this->assertSame('visualização', $registros->first()->alteracoes['detalhes']['pagina']);
        $this->assertSame('Aluno: Aluno Auditado', $registros->first()->descricao);
    }
}