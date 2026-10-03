<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pernoites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->date('data_pernoite');
            $table->boolean('parcial')->default(false);
            $table->text('justificativa');
            $table->string('forma_autorizacao', 50)->nullable();
            $table->string('quem_autorizou', 100);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index(['aluno_id', 'data_pernoite']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pernoites');
    }
};