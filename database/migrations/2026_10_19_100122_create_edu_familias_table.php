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
        Schema::create('edu_familias', function (Blueprint $table) {
            // Um código por linha (CODFILIACAO no RM): cada linha liga um familiar (cod_pessoa) a um aluno (cod_pessoa_aluno).
            $table->unsignedBigInteger('cod_familia')->primary();

            $table->foreignId('cod_pessoa')
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_pessoa_aluno')
                ->constrained('inst_pessoas', 'cod_pessoa');

            // ADD 13/11/2026
            $table->foreignId('cod_parentesco')
                ->constrained('aux_parentesco', 'cod_parentesco');
            $table->string('parentesco', 25);

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['cod_familia', 'cod_pessoa'], 'ix_familias_pessoas_cod_familia_cod_pessoa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_familias');
    }
};
