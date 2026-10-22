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
        Schema::create('fun_turma_disciplina_funcionarios', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_turma_disciplina_funcionario')->primary();

            $table->foreignId('cod_turma_disciplina')
                ->constrained('edu_turma_disciplinas', 'cod_turma_disciplina');
            $table->foreignId('cod_funcionario')
                ->constrained('fun_funcionarios', 'cod_funcionario');

            $table->string('tipo_atribuicao', 60)->nullable();
            $table->string('codigo_papel_origem', 30)->nullable();
            $table->dateTime('data_inicio')->nullable();
            $table->dateTime('data_fim')->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['cod_turma_disciplina', 'cod_funcionario'],
                'ix_turma_disciplina_funcionarios_cod_turma_disciplina_cod_func'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fun_turma_disciplina_funcionarios');
    }
};
