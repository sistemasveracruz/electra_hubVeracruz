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
        Schema::create('fun_vinculos_funcionarios', function (Blueprint $table) {
            $table->bigIncrements('cod_vinculo_funcionario');

            $table->foreignId('cod_funcionario')
                ->constrained('fun_funcionarios', 'cod_funcionario');
            $table->foreignId('cod_escola')
                ->constrained('inst_escolas', 'cod_escola');
            $table->foreignId('cod_unidade_escolar')
                ->constrained('inst_unidades_escolares', 'cod_unidade_escolar');

            $table->string('cargo', 120)->nullable();
            $table->string('setor', 120)->nullable();
            $table->string('tipo_vinculo', 50)->nullable();
            $table->dateTime('data_admissao')->nullable();
            $table->dateTime('data_desligamento')->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['cod_funcionario', 'cod_escola'],
                'ix_vinculos_funcionais_cod_funcionario_cod_escola'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fun_vinculos_funcionarios');
    }
};
