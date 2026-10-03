<?php

namespace Tests\Feature;

use App\Models\Residencia;
use App\Models\TrocaApartamento;
use Tests\TestCase;

class ResidenciaTrocasTest extends TestCase
{
    public function test_primeira_atribuicao_de_apartamento_nao_e_troca(): void
    {
        $residencia = Residencia::create([
            'aluno_id' => $this->criarAluno()->id,
            'categoria' => 'residente',
            'apartamento_id' => $this->criarApartamento()->id,
        ]);

        $this->assertSame(0, TrocaApartamento::count());
        $this->assertNotNull($residencia->apartamento_id);
    }

    public function test_mudar_de_apartamento_grava_o_historico(): void
    {
        $a = $this->criarApartamento();
        $b = $this->criarApartamento();
        $aluno = $this->criarAluno();

        $residencia = Residencia::create(['aluno_id' => $aluno->id, 'categoria' => 'residente', 'apartamento_id' => $a->id]);
        $residencia->update(['apartamento_id' => $b->id]);

        $troca = TrocaApartamento::firstOrFail();
        $this->assertSame($aluno->id, $troca->aluno_id);
        $this->assertSame($a->id, $troca->origem_apartamento_id);
        $this->assertSame($b->id, $troca->destino_apartamento_id);
    }

    public function test_sair_do_apartamento_tambem_entra_no_historico(): void
    {
        $a = $this->criarApartamento();

        $residencia = Residencia::create([
            'aluno_id' => $this->criarAluno()->id,
            'categoria' => 'semirresidente',
            'apartamento_id' => $a->id,
        ]);
        $residencia->update(['apartamento_id' => null]);

        $troca = TrocaApartamento::firstOrFail();
        $this->assertSame($a->id, $troca->origem_apartamento_id);
        $this->assertNull($troca->destino_apartamento_id);
    }

    public function test_alterar_outros_campos_nao_gera_troca(): void
    {
        $residencia = Residencia::create([
            'aluno_id' => $this->criarAluno()->id,
            'categoria' => 'residente',
            'apartamento_id' => $this->criarApartamento()->id,
        ]);
        $residencia->update(['ocorrencias' => 'Texto qualquer']);

        $this->assertSame(0, TrocaApartamento::count());
    }
}