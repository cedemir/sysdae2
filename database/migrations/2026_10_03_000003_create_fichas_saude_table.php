<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichas_saude', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->unique()->constrained('alunos')->cascadeOnDelete();
            $table->string('tipo_sanguineo', 20)->default('nao_informado');
            $table->text('alergias')->nullable();
            $table->text('condicoes_saude')->nullable();
            $table->text('medicamentos_uso')->nullable();
            $table->text('restricoes_alimentares')->nullable();
            $table->text('necessidades_especiais')->nullable();
            $table->char('cartao_sus', 15)->nullable();
            $table->string('plano_saude', 100)->nullable();
            $table->string('unidade_referencia', 150)->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichas_saude');
    }
};