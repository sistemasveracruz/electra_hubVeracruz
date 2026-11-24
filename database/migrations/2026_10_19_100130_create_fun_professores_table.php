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
        Schema::create('fun_professores', function (Blueprint $table) {
            $table->bigIncrements('codigo');

            // SPROFESSOR.CODPROF: repete quando o professor tem mais de uma chapa.
            $table->unsignedBigInteger('cod_professor');

            $table->foreignId('cod_pessoa')
                ->constrained('inst_pessoas', 'cod_pessoa');

            // Aponta para o índice único de fun_funcionarios.chapa.
            $table->string('chapa', 20);
            $table->foreign('chapa')
                ->references('chapa')
                ->on('fun_funcionarios');

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['cod_professor', 'chapa'], 'uq_professores_cod_professor_chapa');
            $table->index('cod_pessoa', 'ix_professores_cod_pessoa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fun_professores');
    }
};
