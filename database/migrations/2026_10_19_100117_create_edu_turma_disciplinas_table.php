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
        Schema::create('edu_turma_disciplinas', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_turma_disciplina')->primary();

            $table->foreignId('cod_turma')
                ->constrained('edu_turmas', 'cod_turma');
            $table->foreignId('cod_disciplina')
                ->constrained('edu_disciplinas', 'cod_disciplina');

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['cod_disciplina', 'cod_turma'], 'uk_turma_disciplinas_disciplina_turma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_turma_disciplinas');
    }
};
