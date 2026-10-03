<?php

namespace Tests\Feature;

use App\Filament\Resources\Alunos\Pages\CreateAluno;
use App\Filament\Resources\Residencias\Pages\CreateResidencia;
use App\Models\Aluno;
use App\Models\Residencia;
use App\Support\Perfis;
use Livewire\Livewire;
use Tests\TestCase;

class SexoAlunoTest extends TestCase
{
    public function test_a_lista_de_sexos_tem_prefiro_nao_dizer(): void
    {
        $this->assertSame('Prefiro não dizer', Aluno::SEXOS['nao_dizer']);
        $this->assertArrayHasKey('masculino', Aluno::SEXOS);
        $this->assertArrayHasKey('feminino', Aluno::SEXOS);
    }

    public function test_o_valor_cabe_na_coluna_do_banco(): void
    {
        // A coluna sexo tem 10 caracteres; o valor gravado precisa caber.
        $this->assertLessThanOrEqual(10, strlen('nao_dizer'));

        $aluno = $this->criarAluno(['sexo' => 'nao_dizer']);

        $this->assertSame('nao_dizer', $aluno->fresh()->sexo);
    }

    public function test_o_formulario_aceita_a_nova_opcao(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);

        Livewire::test(CreateAluno::class)
            ->fillForm([
                'nome' => 'Pessoa Sem Informar', 'cpf' => $this->cpf(), 'sexo' => 'nao_dizer',
                'situacao' => 'cursando', 'programa_beneficios' => 'nao_recebe',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('alunos', ['nome' => 'Pessoa Sem Informar', 'sexo' => 'nao_dizer']);
    }

    public function test_o_formulario_continua_recusando_valores_inventados(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);

        Livewire::test(CreateAluno::class)
            ->fillForm([
                'nome' => 'Pessoa Qualquer', 'cpf' => $this->cpf(), 'sexo' => 'outro',
                'situacao' => 'cursando', 'programa_beneficios' => 'nao_recebe',
            ])
            ->call('create')
            ->assertHasFormErrors(['sexo']);
    }

    public function test_quem_prefere_nao_dizer_nao_e_barrado_por_tipo_de_alojamento(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $aluno = $this->criarAluno(['sexo' => 'nao_dizer']);

        foreach (['feminino', 'masculino', 'misto'] as $publico) {
            $apto = $this->criarApartamento($this->criarAlojamento($publico));

            Livewire::test(CreateResidencia::class)
                ->fillForm(['aluno_id' => $aluno->id, 'categoria' => 'residente', 'apartamento_id' => $apto->id])
                ->call('create')
                ->assertHasNoFormErrors();

            Residencia::where('aluno_id', $aluno->id)->delete(); // libera para a próxima tentativa
        }
    }

    public function test_quem_tem_sexo_informado_continua_sendo_barrado_no_alojamento_de_outro_sexo(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $apto = $this->criarApartamento($this->criarAlojamento('feminino'));
        $aluno = $this->criarAluno(['sexo' => 'masculino']);

        Livewire::test(CreateResidencia::class)
            ->fillForm(['aluno_id' => $aluno->id, 'categoria' => 'residente', 'apartamento_id' => $apto->id])
            ->call('create')
            ->assertHasFormErrors(['apartamento_id']);
    }

    public function test_estatisticas_e_ficha_mostram_a_opcao_com_o_nome_certo(): void
    {
        $this->actingAs($this->usuarioComPerfil(Perfis::RESIDENCIA));
        $aluno = $this->criarAluno(['sexo' => 'nao_dizer']);
        Residencia::create([
            'aluno_id' => $aluno->id, 'categoria' => 'residente', 'apartamento_id' => $this->criarApartamento()->id,
        ]);

        $this->get(route('relatorios.gerar', ['tipo' => 'estatisticas']))
            ->assertOk()
            ->assertSee('Prefiro não dizer');

        $this->get(route('relatorios.gerar', ['tipo' => 'ficha', 'cpf' => $aluno->cpf]))
            ->assertOk()
            ->assertSee('Prefiro não dizer');
    }
}