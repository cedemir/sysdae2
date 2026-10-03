<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acessos_perfil', function (Blueprint $table) {
            $table->id();
            $table->string('perfil', 50);
            $table->string('recurso', 50);
            $table->string('nivel', 10);
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();

            $table->unique(['perfil', 'recurso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acessos_perfil');
    }
};