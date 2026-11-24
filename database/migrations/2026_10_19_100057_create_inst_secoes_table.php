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
        // Seções do RM (PSECAO.CODIGO), em árvore: cod_secao_pai aponta para a seção acima.
        Schema::create('inst_secoes', function (Blueprint $table) {
            $table->string('cod_secao', 20)->primary();

            $table->string('descricao', 150);

            $table->string('cod_secao_pai', 20)->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });

        // Auto-referência: a FK só pode ser criada depois que a PK da tabela existir.
        Schema::table('inst_secoes', function (Blueprint $table) {
            $table->foreign('cod_secao_pai')
                ->references('cod_secao')
                ->on('inst_secoes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inst_secoes');
    }
};
