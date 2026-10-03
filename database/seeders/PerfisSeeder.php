<?php

namespace Database\Seeders;

use App\Support\Perfis;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PerfisSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Perfis::TODOS as $perfil) {
            Role::findOrCreate($perfil, 'web');
        }
    }
}