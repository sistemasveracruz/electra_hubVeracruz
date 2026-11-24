<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

            // Alunos menores muitas vezes não têm CPF no RM; o índice único filtrado fica abaixo.
            $table->string('cpf', 11)->nullable();
            $table->string('nome', 150);
            $table->date('data_nascimento')->nullable();
            $table->string('email', 250)->nullable();
            $table->string('email_pessoal', 250)->nullable();
            $table->string('email_alternativo', 250)->nullable();

            $table->string('telefone_fixo', 20)->nullable();
            $table->string('telefone_pessoal', 20)->nullable();
            $table->string('telefone_empresa', 20)->nullable();

            $table->boolean('falecido')->default(true);
            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['cpf', 'cod_pessoa'], 'ix_pessoas_cpf_cod_pessoa');
        });

        // CPF único quando preenchido: no SQL Server um UNIQUE comum aceitaria um único NULL.
        DB::statement('CREATE UNIQUE INDEX uq_pessoas_cpf ON inst_pessoas (cpf) WHERE cpf IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inst_pessoas');
    }
};
