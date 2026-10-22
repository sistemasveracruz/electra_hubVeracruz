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
        Schema::create('fun_turma_funcionarios', function (Blueprint $table) {
            $table->bigIncrements('cod_turma_funcionario');

            $table->foreignId('cod_turma')
                ->constrained('edu_turmas', 'cod_turma');
            $table->foreignId('cod_funcionario')
                ->constrained('fun_funcionarios', 'cod_funcionario');
            $table->foreignId('cod_funcao_turma')
                ->constrained('fun_funcoes_turma', 'cod_funcao_turma');

            $table->dateTime('data_inicio')->nullable();
            $table->dateTime('data_fim')->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['cod_turma', 'cod_funcionario', 'cod_funcao_turma'],
                'ix_turma_funcionarios_cod_turma_cod_funcionario_cod_fu_3daa9dd5'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fun_turma_funcionarios');
    }
};
