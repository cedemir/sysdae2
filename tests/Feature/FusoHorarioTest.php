<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class FusoHorarioTest extends TestCase
{
    public function test_o_sistema_usa_o_fuso_de_brasilia(): void
    {
        $this->assertSame('America/Sao_Paulo', config('app.timezone'));
        $this->assertSame('America/Sao_Paulo', date_default_timezone_get());
    }

    public function test_agora_esta_tres_horas_atras_do_utc(): void
    {
        // Brasília não tem horário de verão desde 2019: o deslocamento é sempre de -3 horas (-180 minutos).
        $this->assertSame(-180, now()->utcOffset());
    }

    public function test_registros_novos_guardam_a_hora_de_brasilia(): void
    {
        $aluno = $this->criarAluno();

        $this->assertSame(-180, $aluno->created_at->utcOffset());
    }

    public function test_depois_das_21h_o_dia_ainda_e_o_mesmo(): void
    {
        // 02:00 de 02/10 em UTC são 23:00 de 01/10 em Brasília.
        $instante = Carbon::parse('2026-10-02 02:00:00', 'UTC')->setTimezone(config('app.timezone'));

        $this->assertSame('2026-10-01', $instante->toDateString());
        $this->assertSame('23:00', $instante->format('H:i'));
    }
}