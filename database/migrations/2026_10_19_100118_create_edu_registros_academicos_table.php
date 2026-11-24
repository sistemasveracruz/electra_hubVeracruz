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
        Schema::create('edu_registros_academicos', function (Blueprint $table) {
            $table->bigIncrements('cod_registro_academico');

            $table->foreignId('cod_pessoa')
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_escola')
                ->constrained('inst_escolas', 'cod_escola');

            $table->string('registro_academico', 12);

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['cod_pessoa', 'registro_academico'],
                'uq_registros_academicos_cod_escola_registro_academico'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edu_registros_academicos');
    }
};
