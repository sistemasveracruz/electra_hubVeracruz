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
        Schema::create('fun_funcionarios', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_funcionario')->primary();

            $table->foreignId('cod_pessoa')
                ->constrained('inst_pessoas', 'cod_pessoa');

            $table->string('tipo_cadastro', 30)->default('PESSOA');
            $table->boolean('professor_cadastro')->default(false);
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
        Schema::dropIfExists('fun_funcionarios');
    }
};
