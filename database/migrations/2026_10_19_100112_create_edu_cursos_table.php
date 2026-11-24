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
        Schema::create('edu_cursos', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_curso')->primary();

            $table->foreignId('cod_escola')
                ->constrained('inst_escolas', 'cod_escola');
                
            $table->foreignId('cod_tipo_curso')
                ->constrained('edu_tipo_cursos', 'cod_tipo_curso');

            $table->string('nome', 150);
            $table->string('sigla', 12)->nullable();

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
        Schema::dropIfExists('edu_cursos');
    }
};
