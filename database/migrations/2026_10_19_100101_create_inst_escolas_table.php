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
        Schema::create('inst_escolas', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_escola')->primary();

            $table->string('nome', 150);
            $table->string('nome_fantasia', 150)->nullable();
            $table->string('cnpj', 14);
            $table->string('descricao', 250)->nullable();

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
        Schema::dropIfExists('inst_escolas');
    }
};
