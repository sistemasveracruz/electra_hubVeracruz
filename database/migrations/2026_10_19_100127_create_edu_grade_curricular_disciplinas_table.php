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
            $table->bigIncrements('cod_grade_disciplina');

            $table->foreignId('cod_grade_curricular')
                ->constrained('edu_grades_curriculares', 'cod_grade_curricular');

            $table->unsignedBigInteger('cod_etapa')->constrained('edu_etapas', 'cod_etapa');
            $table->unsignedBigInteger('cod_curso')->constrained('edu_cursos', 'cod_curso');

            $table->foreignId('cod_disciplina')
                ->constrained('edu_disciplinas', 'cod_disciplina');

            $table->string('periodo_grade', 30)->nullable();

            $table->decimal('aulas_semanais', 8, 2)->nullable();
            $table->decimal('carga_horaria', 10, 2)->nullable();

            $table->boolean('obrigatoria')->default(true);

            $table->index(
                ['cod_grade_disciplina', 'cod_etapa', 'cod_disciplina', 'periodo_grade'],
                'ix_grades_disciplinas_cod_grade_cod_etapa_cod_discipli_d69ac9c5'
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
