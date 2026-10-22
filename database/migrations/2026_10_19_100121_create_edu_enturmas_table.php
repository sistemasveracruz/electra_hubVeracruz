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
        Schema::create('edu_enturmas', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_enturma')->primary();

            $table->foreignId('cod_aluno')
                ->constrained('edu_alunos', 'cod_aluno');
            $table->foreignId('cod_matricula')
                ->constrained('edu_matriculas', 'cod_matricula');
            $table->foreignId('cod_turma')
                ->constrained('edu_turmas', 'cod_turma');
            $table->foreignId('cod_status')
                ->constrained('edu_status', 'cod_status');

            $table->string('numero_chamada', 120)->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['cod_matricula', 'cod_turma'], 'ix_enturmas_cod_matricula_cod_turma');
            $table->unique(['cod_enturma', 'cod_matricula'], 'uq_enturmas_cod_enturma_cod_matricula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_enturmas');
    }
};
