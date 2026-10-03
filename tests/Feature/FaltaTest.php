<?php

namespace Tests\Feature;

use App\Filament\Resources\Faltas\Pages\CreateFalta;
use App\Models\Falta;
use App\Support\Perfis;
use Livewire\Livewire;
use Tests\TestCase;

class FaltaTest extends TestCase
{
    public function test_falta_repetida_no_mesmo_dia_e_recusada(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $aluno = $this->criarAluno();
        $dia = now()->subDay()->toDateString();

        Falta::create(['aluno_id' => $aluno->id, 'data_falta' => $dia]);

        Livewire::test(CreateFalta::class)
            ->fillForm(['aluno_id' => $aluno->id, 'data_falta' => $dia, 'justificada' => false])
            ->call('create')
            ->assertHasFormErrors(['data_falta']);

        $this->assertSame(1, Falta::count());
    }

    public function test_falta_em_outro_dia_e_aceita(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);
        $aluno = $this->criarAluno();

        Falta::create(['aluno_id' => $aluno->id, 'data_falta' => now()->subDays(2)->toDateString()]);

        Livewire::test(CreateFalta::class)
            ->fillForm(['aluno_id' => $aluno->id, 'data_falta' => now()->subDay()->toDateString(), 'justificada' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, Falta::count());
    }

    public function test_falta_registra_quem_cadastrou(): void
    {
        $usuario = $this->usuarioComPerfil(Perfis::RESIDENCIA);
        $this->actingAs($usuario);

        $falta = Falta::create(['aluno_id' => $this->criarAluno()->id, 'data_falta' => now()->subDay()->toDateString()]);

        $this->assertSame($usuario->id, $falta->user_id);
    }
}