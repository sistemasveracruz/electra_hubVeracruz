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
        // De-para das chaves do TOTVS RM (compostas, muitas varchar) para os cod_* bigint do HUB.
        Schema::create('aux_rm_chaves', function (Blueprint $table) {
            $table->bigIncrements('cod_rm_chave');

            $table->string('entidade', 50);
            $table->string('chave_rm', 200);
            $table->unsignedBigInteger('cod_hub');

            $table->timestamps();

            $table->unique(['entidade', 'chave_rm'], 'uq_rm_chaves_entidade_chave_rm');
            $table->index(['entidade', 'cod_hub'], 'ix_rm_chaves_entidade_cod_hub');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aux_rm_chaves');
    }
};
