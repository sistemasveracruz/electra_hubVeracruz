<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('edu_disciplinas', function (Blueprint $table) {
            $table->bigIncrements('cod_disciplina');

            $table->string('nome', 120);
            $table->string('sigla', 30)->nullable();
            $table->string('observacoes', 250)->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_disciplinas');
    }
};
