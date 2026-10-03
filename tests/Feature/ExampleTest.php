<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_a_pagina_inicial_leva_ao_painel(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }
}
