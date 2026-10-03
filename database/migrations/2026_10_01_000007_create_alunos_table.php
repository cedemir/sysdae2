<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->char('cpf', 11)->unique();
            $table->string('nome', 150);
            $table->string('foto_path')->nullable();
            $table->string('sexo', 10);
            $table->string('email', 150)->nullable();
            $table->string('telefone_estudante', 20)->nullable();
            $table->string('nome_responsaveis', 200)->nullable();
            $table->string('telefone_familia', 20)->nullable();
            $table->string('contato_emergencia', 150)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('programa_beneficios', 30)->default('nao_recebe');
            $table->string('situacao', 20)->default('cursando');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};