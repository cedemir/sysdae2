<?php

namespace Database\Seeders;

use App\Models\Perfil;
use App\Support\Perfis;
use Illuminate\Database\Seeder;

class PerfisSeeder extends Seeder
{
    /** Garante os perfis originais. Não mexe em nome nem em situação de perfis que já existem. */
    public function run(): void
    {
        foreach (Perfis::ROTULOS as $nome => $rotulo) {
            Perfil::firstOrCreate(
                ['name' => $nome, 'guard_name' => 'web'],
                ['rotulo' => $rotulo, 've_sigilosos' => in_array($nome, Perfis::VEEM_SIGILOSOS, true)],
            );
        }
    }
}
