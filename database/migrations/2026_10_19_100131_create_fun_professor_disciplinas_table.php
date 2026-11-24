<?php

use App\Enums\TipoProfessor;
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
        // Uso interno do HUB, sem chave correspondente no TOTVS.
        Schema::create('fun_professor_disciplinas', function (Blueprint $table) {
            $table->bigIncrements('cod_professor_disciplina');

            $table->foreignId('cod_disciplina')
                ->constrained('edu_disciplinas', 'cod_disciplina');
            // Guarda fun_professores.codigo (chave do HUB), não o CODPROF do RM.
            $table->foreignId('cod_professor')
                ->constrained('fun_professores', 'codigo');
            $table->foreignId('cod_turma')
                ->constrained('edu_turmas', 'cod_turma');

            // Papel do professor por extenso (TITULAR, ORIENTADOR...), convertido de SPROFESSORTURMA.TIPOPROF.
            $table->enum('tipo_professor', TipoProfessor::valores())->nullable();

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
        Schema::dropIfExists('fun_professor_disciplinas');
    }
};
