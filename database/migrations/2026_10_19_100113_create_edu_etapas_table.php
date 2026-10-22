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
        Schema::create('edu_etapas', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_etapa');
            $table->foreignId('cod_curso')
                ->constrained('edu_cursos', 'cod_curso');

            $table->string('nome', 150);
            $table->string('sigla', 12)->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->primary(['cod_curso', 'cod_etapa'], 'pk_edu_etapas');

            $table->index('cod_curso', 'ix_etapas_cod_curso');
            $table->unique(['cod_curso', 'cod_etapa'], 'uk_etapas_curso_codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_etapas');
    }
};
