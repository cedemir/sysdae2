<?php

use App\Support\Perfis;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Transforma os perfis (tabela "roles" do spatie) em cadastro: nome de exibição, situação e sigilo. */
return new class extends Migration
{
    public function up(): void
    {
        $tabela = config('permission.table_names.roles', 'roles');

        Schema::table($tabela, function (Blueprint $table) {
            $table->string('rotulo', 100)->nullable()->after('name');
            $table->string('descricao', 255)->nullable()->after('rotulo');
            $table->boolean('ativo')->default(true)->after('descricao');
            $table->boolean('ve_sigilosos')->default(false)->after('ativo');
        });

        foreach (Perfis::ROTULOS as $nome => $rotulo) {
            DB::table($tabela)->where('name', $nome)->update([
                'rotulo' => $rotulo,
                've_sigilosos' => in_array($nome, Perfis::VEEM_SIGILOSOS, true),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table(config('permission.table_names.roles', 'roles'), function (Blueprint $table) {
            $table->dropColumn(['rotulo', 'descricao', 'ativo', 've_sigilosos']);
        });
    }
};
