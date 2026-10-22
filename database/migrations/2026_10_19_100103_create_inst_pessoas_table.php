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
        Schema::create('inst_pessoas', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_pessoa')->primary();

            $table->string('cpf', 11)->unique();
            $table->string('nome', 150);
            $table->date('data_nascimento')->nullable();
            $table->string('email', 250)->nullable();
            $table->string('email_pessoal', 250)->nullable();
            $table->string('email_alternativo', 250)->nullable();

            $table->string('telefone_fixo', 20)->nullable();
            $table->string('telefone_pessoal', 20)->nullable();
            $table->string('telefone_empresa', 20)->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['cpf', 'cod_pessoa'], 'ix_pessoas_cpf_cod_pessoa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inst_pessoas');
    }
};
