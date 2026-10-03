<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IdiomaTest extends TestCase
{
    public function test_o_sistema_usa_portugues_do_brasil(): void
    {
        $this->assertSame('pt_BR', config('app.locale'));
        $this->assertSame('pt_BR', app()->getLocale());
    }

    public function test_mensagens_do_laravel_estao_em_portugues(): void
    {
        $this->assertSame('O campo nome é obrigatório.', __('validation.required', ['attribute' => 'nome']));
        $this->assertSame('O valor informado para e-mail já está em uso.', __('validation.unique', ['attribute' => 'e-mail']));
        $this->assertSame('Essas credenciais não correspondem aos nossos registros.', __('auth.failed'));
    }

    public function test_validador_usa_os_nomes_dos_campos_em_portugues(): void
    {
        $validador = Validator::make(['cpf' => ''], ['cpf' => 'required']);

        $this->assertTrue($validador->fails());
        $this->assertSame('O campo CPF é obrigatório.', $validador->errors()->first('cpf'));
    }

    public function test_textos_do_filament_estao_traduzidos(): void
    {
        $this->assertNotSame('Edit', __('filament-actions::edit.single.label'), 'O botão Editar ainda está em inglês.');
        $this->assertNotSame('Delete', __('filament-actions::delete.single.label'), 'O botão Excluir ainda está em inglês.');
    }

    public function test_o_painel_se_chama_painel(): void
    {
        $this->assertSame('Painel', __('filament-panels::pages/dashboard.title'));
    }

    public function test_datas_saem_em_portugues(): void
    {
        $this->assertSame('março', Carbon::create(2026, 3, 10)->translatedFormat('F'));
    }
}