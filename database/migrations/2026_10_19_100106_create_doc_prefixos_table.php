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
        Schema::create('doc_prefixos', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_prefixo')->primary();

            $table->char('sigla', 6)->unique();
            $table->string('descricao', 250)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_prefixos');
    }
};
