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
        Schema::create('edu_grade_curricular_disciplinas', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_grade_disciplina')->primary();

            $table->foreignId('cod_grade_curricular')
                ->constrained('edu_grades_curriculares', 'cod_grade_curricular');

            $table->foreignId('cod_curso')
                ->constrained('edu_cursos', 'cod_curso');
            $table->unsignedBigInteger('cod_etapa');
            $table->foreign(['cod_curso', 'cod_etapa'], 'fk_grade_curricular_disciplinas_etapa')
                ->references(['cod_curso', 'cod_etapa'])
                ->on('edu_etapas');
            $table->foreignId('cod_disciplina')
                ->constrained('edu_disciplinas', 'cod_disciplina');

            $table->foreignId('cod_turno')
                ->nullable()
                ->constrained('edu_turnos', 'cod_turno');
            $table->string('periodo_grade', 30)->nullable();

            $table->decimal('aulas_semanais', 8, 2)->nullable();
            $table->decimal('quantidade_semanas', 8, 2)->nullable();
            $table->decimal('carga_horaria', 10, 2)->nullable();

            $table->boolean('obrigatoria')->default(true);

            $table->index(
                ['cod_grade_disciplina', 'cod_etapa', 'cod_disciplina', 'cod_turno', 'periodo_grade'],
                'ix_grades_disciplinas_cod_grade_cod_etapa_cod_disciplina'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_grade_curricular_disciplinas');
    }
};
