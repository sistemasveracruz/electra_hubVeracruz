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
        // Uma linha por contrato (PFUNC.CHAPA): a mesma pessoa pode ter mais de uma chapa.
        Schema::create('fun_funcionarios', function (Blueprint $table) {
            $table->bigIncrements('cod_funcionario');

            $table->string('chapa', 20)->unique();

            $table->foreignId('cod_pessoa')
                ->constrained('inst_pessoas', 'cod_pessoa');

            $table->string('tipo_cadastro', 30)->default('PESSOA');

            $table->string('cod_secao', 20);
            $table->foreign('cod_secao')
                ->references('cod_secao')
                ->on('inst_secoes');

            $table->string('cod_funcao', 20);
            $table->foreign('cod_funcao')
                ->references('cod_funcao')
                ->on('fun_funcoes');

            $table->char('cod_fun_situacao', 1);
            $table->foreign('cod_fun_situacao')
                ->references('cod_fun_situacao')
                ->on('fun_situacao');

            $table->dateTime('data_admissao')->nullable();
            $table->dateTime('data_desligamento')->nullable();

            // true quando PFUNC.CODSITUACAO está em A, E, F, G, W ou Y.
            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['cod_pessoa', 'chapa'], 'ix_funcionarios_cod_pessoa_chapa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fun_funcionarios');
    }
};
