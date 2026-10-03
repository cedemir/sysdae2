<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->unique()->constrained('alunos')->cascadeOnDelete();
            $table->string('categoria', 20);
            $table->foreignId('apartamento_id')->nullable()->constrained('apartamentos')->restrictOnDelete();
            $table->foreignId('regime_id')->nullable()->constrained('regimes')->restrictOnDelete();
            $table->date('data_entrada')->nullable();
            $table->text('ocorrencias')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residencias');
    }
};