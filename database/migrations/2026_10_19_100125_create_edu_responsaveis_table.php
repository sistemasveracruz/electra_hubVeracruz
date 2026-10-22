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
        Schema::create('edu_responsaveis', function (Blueprint $table) {
            $table->foreignId('cod_aluno')
                ->constrained('edu_alunos', 'cod_aluno');
            $table->foreignId('cod_pessoa_aluno')
                ->constrained('inst_pessoas', 'cod_pessoa');

            $table->foreignId('cod_responsavel_pedagogico')
                ->constrained('edu_responsaveis_pedagogico', 'cod_responsavel_pedagogico');
            $table->foreignId('cod_responsavel_financeiro')
                ->constrained('edu_responsaveis_financeiros', 'cod_responsavel_financeiro');

            $table->timestamps();

            $table->primary(
                ['cod_aluno', 'cod_responsavel_pedagogico', 'cod_responsavel_financeiro'],
                'pk_edu_responsaveis'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_responsaveis');
    }
};
