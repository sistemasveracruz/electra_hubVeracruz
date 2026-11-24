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
        Schema::create('edu_turmas', function (Blueprint $table) {
            $table->bigIncrements('cod_turma');

            $table->string('codigo_turma', 20);

            $table->foreignId('cod_unidade_escolar')
                ->constrained('inst_unidades_escolares', 'cod_unidade_escolar');
            $table->foreignId('cod_periodo_letivo')
                ->constrained('edu_periodos_letivos', 'cod_periodo_letivo');
            $table->foreignId('cod_curso')
                ->constrained('edu_cursos', 'cod_curso');
            $table->unsignedBigInteger('cod_etapa');
            $table->foreign(['cod_curso', 'cod_etapa'], 'fk_turmas_etapa')
                ->references(['cod_curso', 'cod_etapa'])
                ->on('edu_etapas');
            $table->foreignId('cod_sala')
                ->nullable()
                ->constrained('edu_salas', 'cod_sala');
            $table->foreignId('cod_turno')
                ->constrained('edu_turnos', 'cod_turno');

            $table->foreignId('cod_pessoa_coordenador')
                ->nullable()
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_pessoa_orientador')
                ->nullable()
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_pessoa_professor_responsavel')
                ->nullable()
                ->constrained('inst_pessoas', 'cod_pessoa');

            $table->string('nome', 120);
            $table->string('nome_reduzido', 12);

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['cod_periodo_letivo', 'codigo_turma', 'cod_etapa', 'cod_turno'],
                'ix_turmas_cod_periodo_letivo_codigo_turma_cod_etapa_cod_turno'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_turmas');
    }
};
