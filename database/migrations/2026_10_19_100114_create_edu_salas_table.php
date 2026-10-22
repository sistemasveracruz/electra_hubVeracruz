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
        Schema::create('edu_salas', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_sala')->primary();

            $table->foreignId('cod_unidade_escolar')
                ->constrained('inst_unidades_escolares', 'cod_unidade_escolar');
            $table->foreignId('cod_tipo_sala')
                ->constrained('edu_tipo_salas', 'cod_tipo_sala');

            $table->string('nome', 120);
            $table->string('descricao', 250)->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['cod_unidade_escolar', 'nome'], 'ix_salas_cod_unidade_nome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_salas');
    }
};
