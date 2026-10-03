<?php

namespace Tests\Feature;

use App\Filament\Resources\Atendimentos\AtendimentoResource;
use App\Filament\Resources\Cursos\CursoResource;
use App\Filament\Resources\FichaSaudes\FichaSaudeResource;
use App\Models\Atendimento;
use App\Models\Auditoria;
use App\Models\FichaSaude;
use App\Support\Perfis;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConsultaSensivelTest extends TestCase
{
    private function consultas()
    {
        return Auditoria::where('evento', 'consultou')->orderBy('id')->get();
    }

    private function criarFicha(string $nome = 'Aluno Consultado'): FichaSaude
    {
        $aluno = $this->criarAluno(['nome' => $nome]);
        $ficha = FichaSaude::create(['aluno_id' => $aluno->id, 'alergias' => 'dado reservado']);
        DB::table('auditorias')->delete(); // zera a auditoria criada pela preparação do teste

        return $ficha;
    }

    public function test_abrir_uma_ficha_de_saude_e_registrado(): void
    {
        $admin = $this->entrarNoPainel(Perfis::ADMIN);
        $ficha = $this->criarFicha();

        $this->get(FichaSaudeResource::getUrl('edit', ['record' => $ficha]))->assertOk();

        $registros = $this->consultas();
        $this->assertCount(1, $registros);
        $this->assertSame('Ficha de saúde', $registros->first()->entidade);
        $this->assertSame($ficha->id, $registros->first()->registro_id);
        $this->assertSame('Aluno: Aluno Consultado', $registros->first()->descricao);
        $this->assertSame($admin->id, $registros->first()->user_id);
        $this->assertSame('edição', $registros->first()->alteracoes['detalhes']['pagina']);
    }

    public function test_abrir_a_lista_tambem_e_registrado(): void
    {
        $this->entrarNoPainel(Perfis::PSICOSSOCIAL);
        $this->criarFicha();

        $this->get(FichaSaudeResource::getUrl('index'))->assertOk();

        $registros = $this->consultas();
        $this->assertCount(1, $registros);
        $this->assertNull($registros->first()->registro_id);
        $this->assertSame('lista', $registros->first()->alteracoes['detalhes']['pagina']);
    }

    public function test_abrir_um_atendimento_e_registrado(): void
    {
        $this->entrarNoPainel(Perfis::PSICOSSOCIAL);
        $aluno = $this->criarAluno(['nome' => 'Aluno Atendido']);
        $atendimento = Atendimento::create([
            'aluno_id' => $aluno->id, 'data_atendimento' => '2026-03-10', 'servidores' => 'Equipe',
            'forma' => 'presencial', 'relato' => 'texto reservado',
        ]);
        DB::table('auditorias')->delete();

        $this->get(AtendimentoResource::getUrl('edit', ['record' => $atendimento]))->assertOk();

        $registros = $this->consultas();
        $this->assertCount(1, $registros);
        $this->assertSame('Atendimento psicossocial', $registros->first()->entidade);
        $this->assertSame('Aluno: Aluno Atendido', $registros->first()->descricao);
    }

    public function test_abrir_varias_vezes_seguidas_conta_uma_vez_so(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $ficha = $this->criarFicha();
        $url = FichaSaudeResource::getUrl('edit', ['record' => $ficha]);

        $this->get($url)->assertOk();
        $this->get($url)->assertOk();
        $this->assertCount(1, $this->consultas());

        $this->travel(6)->minutes();
        $this->get($url)->assertOk();
        $this->assertCount(2, $this->consultas());
    }

    public function test_cada_ficha_aberta_gera_o_seu_proprio_registro(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $primeira = $this->criarFicha('Primeiro Aluno');
        $segunda = FichaSaude::create(['aluno_id' => $this->criarAluno(['nome' => 'Segundo Aluno'])->id]);
        DB::table('auditorias')->delete();

        $this->get(FichaSaudeResource::getUrl('edit', ['record' => $primeira]))->assertOk();
        $this->get(FichaSaudeResource::getUrl('edit', ['record' => $segunda]))->assertOk();

        $this->assertSame(
            ['Aluno: Primeiro Aluno', 'Aluno: Segundo Aluno'],
            $this->consultas()->pluck('descricao')->all()
        );
    }

    public function test_telas_comuns_nao_geram_registro_de_consulta(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);

        $this->get(CursoResource::getUrl('index'))->assertOk();

        $this->assertCount(0, $this->consultas());
    }

    public function test_acesso_negado_nao_e_consulta(): void
    {
        $this->entrarNoPainel(Perfis::DAE_CENTRAL);
        $this->criarFicha();

        $this->get(FichaSaudeResource::getUrl('index'))->assertForbidden();

        $this->assertCount(0, $this->consultas());
    }

    public function test_o_evento_tem_nome_em_portugues(): void
    {
        $this->assertSame('Consultou', Auditoria::EVENTOS['consultou']);
    }
}