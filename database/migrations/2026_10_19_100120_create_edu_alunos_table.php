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
        Schema::create('edu_alunos', function (Blueprint $table) {
            $table->bigIncrements('cod_aluno');

            $table->foreignId('cod_pessoa')
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_registro_academico')
                ->constrained('edu_registros_academicos', 'cod_registro_academico');
            $table->foreignId('cod_matricula')
                ->constrained('edu_matriculas', 'cod_matricula');

            // ADD 13/11/2026
            $table->foreignId('cod_pessoa_pai')
                ->nullable()
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_pessoa_mae')
                ->nullable()
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_pessoa_responsavel_pedagogico')
                ->nullable()
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_pessoa_responsavel_financeiro')
                ->nullable()
                ->constrained('inst_pessoas', 'cod_pessoa');

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
        Schema::dropIfExists('edu_alunos');
    }
};
