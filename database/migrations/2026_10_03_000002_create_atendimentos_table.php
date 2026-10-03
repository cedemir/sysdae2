<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atendimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->date('data_atendimento');
            $table->time('hora_atendimento')->nullable();
            $table->string('servidores', 200);
            $table->string('forma', 20);
            $table->text('relato');
            $table->text('outras_observacoes')->nullable();
            $table->text('historia_vida')->nullable();
            $table->text('encaminhamentos')->nullable();
            $table->boolean('sigiloso')->default(true);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index(['aluno_id', 'data_atendimento']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimentos');
    }
};