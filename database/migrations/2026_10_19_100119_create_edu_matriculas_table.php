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
        Schema::create('edu_matriculas', function (Blueprint $table) {
            $table->bigIncrements('cod_matricula');

            $table->foreignId('cod_pessoa')
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_escola')
                ->constrained('inst_escolas', 'cod_escola');
            $table->foreignId('cod_unidade_escolar')
                ->constrained('inst_unidades_escolares', 'cod_unidade_escolar');

            $table->foreignId('cod_curso')
                ->constrained('edu_cursos', 'cod_curso');
            $table->unsignedBigInteger('cod_etapa');
            $table->foreign(['cod_curso', 'cod_etapa'], 'fk_matriculas_etapa')
                ->references(['cod_curso', 'cod_etapa'])
                ->on('edu_etapas');
            $table->foreignId('cod_turma')
                ->constrained('edu_turmas', 'cod_turma');

            $table->foreignId('cod_status')
                ->constrained('edu_status', 'cod_status');

            $table->foreignId('cod_registro_academico')
                ->constrained('edu_registros_academicos', 'cod_registro_academico');

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
        Schema::dropIfExists('edu_matriculas');
    }
};
