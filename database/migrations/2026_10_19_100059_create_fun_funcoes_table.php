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
        // Funções (cargos) do RM: PFUNCAO.CODIGO.
        Schema::create('fun_funcoes', function (Blueprint $table) {
            $table->string('cod_funcao', 20)->primary();

            $table->string('nome', 150);

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
        Schema::dropIfExists('fun_funcoes');
    }
};
