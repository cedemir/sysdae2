<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trocas_apartamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('origem_apartamento_id')->nullable()->constrained('apartamentos')->restrictOnDelete();
            $table->foreignId('destino_apartamento_id')->nullable()->constrained('apartamentos')->restrictOnDelete();
            $table->date('data_troca');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trocas_apartamento');
    }
};