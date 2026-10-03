<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ocorrencias', function (Blueprint $table) {
            $table->json('anexos')->nullable()->after('outros_encaminhamentos');
            $table->json('anexos_nomes')->nullable()->after('anexos');
        });
    }

    public function down(): void
    {
        Schema::table('ocorrencias', function (Blueprint $table) {
            $table->dropColumn(['anexos', 'anexos_nomes']);
        });
    }
};