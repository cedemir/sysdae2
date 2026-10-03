<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->date('data_reuniao');
            $table->string('assunto', 200);
            $table->text('participantes')->nullable();
            $table->text('pauta')->nullable();
            $table->text('deliberacoes')->nullable();
            $table->text('encaminhamentos')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atas');
    }
};