<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocorrencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->date('data_ocorrencia');
            $table->text('descricao');
            $table->boolean('sigiloso')->default(false);
            $table->date('data_reuniao')->nullable();
            $table->text('medidas')->nullable();
            $table->string('advertencia', 10)->nullable();
            $table->boolean('suspensao_residencia')->default(false);
            $table->string('perda_vaga', 10)->nullable();
            $table->text('atividades_orientadas')->nullable();
            $table->unsignedSmallInteger('horas_recebidas')->nullable();
            $table->unsignedSmallInteger('horas_cumpridas')->nullable();
            $table->string('setor', 100)->nullable();
            $table->string('servidor', 100)->nullable();
            $table->text('praticas_restaurativas')->nullable();
            $table->text('outros_encaminhamentos')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index(['aluno_id', 'data_ocorrencia']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocorrencias');
    }
};