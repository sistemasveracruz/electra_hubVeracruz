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
        Schema::create('edu_periodos_letivos', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_periodo_letivo')->primary();

            $table->foreignId('cod_unidade_escolar')
                ->constrained('inst_unidades_escolares', 'cod_unidade_escolar');
            $table->foreignId('cod_tipo_curso')
                ->constrained('edu_tipo_cursos', 'cod_tipo_curso');

            $table->unsignedBigInteger('cod_periodo_letivo_anterior')->nullable();

            $table->dateTime('data_inicio')->nullable();
            $table->dateTime('data_fim')->nullable();
            $table->dateTime('data_estimada')->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });

        // Auto-referência: a FK só pode ser criada depois que a PK da tabela existir.
        Schema::table('edu_periodos_letivos', function (Blueprint $table) {
            $table->foreign('cod_periodo_letivo_anterior')
                ->references('cod_periodo_letivo')
                ->on('edu_periodos_letivos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_periodos_letivos');
    }
};
