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
        Schema::create('edu_aplicacoes_curriculares', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_aplicacao_curricular')->primary();

            $table->foreignId('cod_grade_curricular')
                ->constrained('edu_grades_curriculares', 'cod_grade_curricular');
            $table->foreignId('cod_unidade_escolar')
                ->nullable()
                ->constrained('inst_unidades_escolares', 'cod_unidade_escolar');
            $table->foreignId('cod_curso')
                ->nullable()
                ->constrained('edu_cursos', 'cod_curso');
            $table->unsignedBigInteger('cod_etapa')->nullable();
            $table->foreign(['cod_curso', 'cod_etapa'], 'fk_aplicacoes_curriculares_etapa')
                ->references(['cod_curso', 'cod_etapa'])
                ->on('edu_etapas');
            $table->foreignId('cod_turno')
                ->nullable()
                ->constrained('edu_turnos', 'cod_turno');

            $table->string('regime_grade', 30)->nullable();
            $table->string('periodo_grade', 30)->nullable();

            $table->dateTime('data_inicio')->nullable();
            $table->dateTime('data_fim')->nullable();

            $table->index(
                ['cod_grade_curricular', 'cod_unidade_escolar', 'cod_etapa', 'cod_turno'],
                'ix_aplicacoes_curriculares_cod_grade_cod_unidade_cod_etapa'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_aplicacoes_curriculares');
    }
};
