<?php

namespace Tests\Feature;

use App\Filament\Pages\Painel;
use App\Support\Perfis;
use Tests\TestCase;

class PainelTest extends TestCase
{
    public function test_o_painel_mostra_o_credito_do_sistema(): void
    {
        $this->entrarNoPainel(Perfis::RESIDENCIA);

        $this->get(Painel::getUrl())
            ->assertOk()
            ->assertSee('Sysdae desenvolvido para o DAE por Cedemir Pereira')
            ->assertDontSee('fi-filament-info-widget', false);
    }
}
