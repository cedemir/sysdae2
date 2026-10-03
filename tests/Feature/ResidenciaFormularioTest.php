<?php

namespace Tests\Feature;

use App\Filament\Resources\Residencias\Pages\CreateResidencia;
use App\Models\Residencia;
use App\Support\Perfis;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regras do formulário de Residência (lotação, sexo do alojamento, apartamento obrigatório).
 * Usam os recursos de teste do Filament; se algum método falhar por diferença de versão, avise.
 */
class ResidenciaFormularioTest extends TestCase
{
    public function test_apartamento_lotado_e_recusado(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $apto = $this->criarApartamento($this->criarAlojamento('misto'), 1);

        Residencia::create(['aluno_id' => $this->criarAluno()->id, 'categoria' => 'residente', 'apartamento_id' => $apto->id]);
        $segundo = $this->criarAluno();

        Livewire::test(CreateResidencia::class)
            ->fillForm(['aluno_id' => $segundo->id, 'categoria' => 'residente', 'apartamento_id' => $apto->id])
            ->call('create')
            ->assertHasFormErrors(['apartamento_id']);

        $this->assertSame(1, Residencia::count());
    }

    public function test_aluno_em_alojamento_de_outro_sexo_e_recusado(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $apto = $this->criarApartamento($this->criarAlojamento('feminino'));
        $aluno = $this->criarAluno(['sexo' => 'masculino']);

        Livewire::test(CreateResidencia::class)
            ->fillForm(['aluno_id' => $aluno->id, 'categoria' => 'residente', 'apartamento_id' => $apto->id])
            ->call('create')
            ->assertHasFormErrors(['apartamento_id']);

        $this->assertSame(0, Residencia::count());
    }

    public function test_residente_precisa_de_apartamento(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);

        Livewire::test(CreateResidencia::class)
            ->fillForm(['aluno_id' => $this->criarAluno()->id, 'categoria' => 'residente'])
            ->call('create')
            ->assertHasFormErrors(['apartamento_id' => 'required']);
    }

    public function test_cadastro_valido_e_salvo(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $apto = $this->criarApartamento($this->criarAlojamento('masculino'));
        $aluno = $this->criarAluno(['sexo' => 'masculino']);

        Livewire::test(CreateResidencia::class)
            ->fillForm(['aluno_id' => $aluno->id, 'categoria' => 'residente', 'apartamento_id' => $apto->id])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('residencias', ['aluno_id' => $aluno->id, 'apartamento_id' => $apto->id]);
    }
}