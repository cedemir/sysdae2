<?php

namespace Tests\Unit;

use App\Rules\Cpf;
use PHPUnit\Framework\TestCase;

class CpfTest extends TestCase
{
    public function test_aceita_cpfs_validos(): void
    {
        foreach (['52998224725', '11144477735'] as $cpf) {
            $this->assertTrue(Cpf::valido($cpf), "O CPF {$cpf} deveria ser válido.");
        }
    }

    public function test_recusa_cpfs_invalidos(): void
    {
        foreach (['11111111111', '00000000000', '12345678900', '52998224724', '1234'] as $cpf) {
            $this->assertFalse(Cpf::valido($cpf), "O CPF {$cpf} deveria ser inválido.");
        }
    }
}