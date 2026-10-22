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
        Schema::create('edu_responsaveis_financeiros', function (Blueprint $table) {
            $table->bigIncrements('cod_responsavel_financeiro');

            $table->foreignId('cod_pessoa_aluno')
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_pessoa_responsavel')
                ->constrained('inst_pessoas', 'cod_pessoa');
            $table->foreignId('cod_parentesco_responsavel')
                ->nullable()
                ->constrained('aux_parentesco', 'cod_parentesco');

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
        Schema::dropIfExists('edu_responsaveis_financeiros');
    }
};
