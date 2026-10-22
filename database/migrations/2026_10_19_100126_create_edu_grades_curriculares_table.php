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
        Schema::create('edu_grades_curriculares', function (Blueprint $table) {
            $table->bigIncrements('cod_grade_curricular');

            $table->string('cod_grade', 20);

            $table->foreignId('cod_curso')
                ->constrained('edu_cursos', 'cod_curso');
            $table->unsignedBigInteger('cod_etapa');
            $table->foreign(['cod_curso', 'cod_etapa'], 'fk_grades_curriculares_etapa')
                ->references(['cod_curso', 'cod_etapa'])
                ->on('edu_etapas');

            $table->string('descricao');

            $table->dateTime('data_inicio')->nullable();
            $table->dateTime('data_fim')->nullable();

            $table->decimal('carga_horaria', 10, 2)->nullable();

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
        Schema::dropIfExists('edu_grades_curriculares');
    }
};
