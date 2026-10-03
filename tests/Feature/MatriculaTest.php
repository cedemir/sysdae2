<?php

namespace Tests\Feature;

use App\Filament\Resources\Matriculas\Pages\CreateMatricula;
use App\Filament\Resources\Matriculas\MatriculaResource;
use App\Models\Matricula;
use App\Support\Formatos;
use App\Support\Perfis;
use Livewire\Livewire;
use Tests\TestCase;

class MatriculaTest extends TestCase
{
    private function matricular(int $alunoId, int $turmaId, string $numero, string $situacao = 'cursando'): Matricula
    {
        return Matricula::create([
            'aluno_id' => $alunoId,
            'turma_id' => $turmaId,
            'numero' => $numero,
            'data_matricula' => '2026-02-01',
            'situacao' => $situacao,
        ]);
    }

    public function test_situacao_da_matricula_mais_recente_vai_para_o_aluno(): void
    {
        $aluno = $this->criarAluno();

        $this->matricular($aluno->id, $this->criarTurma()->id, 'M1', 'trancamento');

        $this->assertSame('trancamento', $aluno->fresh()->situacao);
    }

    public function test_detalhe_da_matricula_mostra_nomes_em_vez_de_ids(): void
    {
        $this->entrarNoPainel(Perfis::ADMIN);
        $aluno = $this->criarAluno(['nome' => 'Aluno da Matricula']);
        $curso = $this->criarCurso(['nome' => 'Curso da Matricula']);
        $turma = $this->criarTurma($curso, ['codigo' => 'TURMA-MATRICULA']);
        $matricula = $this->matricular($aluno->id, $turma->id, 'MAT-DETALHE');

        $this->get(MatriculaResource::getUrl('view', ['record' => $matricula]))
            ->assertOk()
            ->assertSee('Aluno da Matricula')
            ->assertSee(Formatos::cpf($aluno->cpf))
            ->assertSee('TURMA-MATRICULA')
            ->assertSee('Curso da Matricula');
    }

    public function test_so_uma_matricula_cursando_por_aluno(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $aluno = $this->criarAluno();
        $this->matricular($aluno->id, $this->criarTurma()->id, 'M1');

        Livewire::test(CreateMatricula::class)
            ->fillForm([
                'aluno_id' => $aluno->id, 'numero' => 'M1', 'turma_id' => $this->criarTurma()->id,
                'data_matricula' => '2026-03-01', 'situacao' => 'cursando',
            ])
            ->call('create')
            ->assertHasFormErrors(['situacao']);
    }

    public function test_depois_de_encerrar_a_matricula_o_aluno_pode_se_matricular_de_novo(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $aluno = $this->criarAluno();
        $this->matricular($aluno->id, $this->criarTurma()->id, 'M1', 'transferido');

        Livewire::test(CreateMatricula::class)
            ->fillForm([
                'aluno_id' => $aluno->id, 'numero' => 'M1', 'turma_id' => $this->criarTurma()->id,
                'data_matricula' => '2026-03-01', 'situacao' => 'cursando',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, Matricula::where('aluno_id', $aluno->id)->count());
    }

    public function test_numero_de_matricula_de_outro_aluno_e_recusado(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $this->matricular($this->criarAluno()->id, $this->criarTurma()->id, 'M100');
        $outro = $this->criarAluno();

        Livewire::test(CreateMatricula::class)
            ->fillForm([
                'aluno_id' => $outro->id, 'numero' => 'M100', 'turma_id' => $this->criarTurma()->id,
                'data_matricula' => '2026-03-01', 'situacao' => 'cursando',
            ])
            ->call('create')
            ->assertHasFormErrors(['numero']);
    }

    public function test_turma_inativa_e_recusada_em_nova_matricula(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $turmaInativa = $this->criarTurma(null, ['ativa' => false]);

        Livewire::test(CreateMatricula::class)
            ->fillForm([
                'aluno_id' => $this->criarAluno()->id, 'numero' => 'M7', 'turma_id' => $turmaInativa->id,
                'data_matricula' => '2026-03-01', 'situacao' => 'cursando',
            ])
            ->call('create')
            ->assertHasFormErrors(['turma_id']);
    }
}