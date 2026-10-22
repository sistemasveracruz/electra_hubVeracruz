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
        Schema::create('fun_funcoes_turma', function (Blueprint $table) {
            $table->bigIncrements('cod_funcao_turma');

            $table->string('codigo', 50);
            $table->string('descricao', 150);

            $table->unique('codigo', 'uq_funcoes_turma_codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fun_funcoes_turma');
    }
};
