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
        // Situações do funcionário no RM (PCODSITUACAO.CODINTERNO): A, D, E, F...
        Schema::create('fun_situacao', function (Blueprint $table) {
            $table->char('cod_fun_situacao', 1)->primary();

            $table->string('descricao', 150);

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
        Schema::dropIfExists('fun_situacao');
    }
};
