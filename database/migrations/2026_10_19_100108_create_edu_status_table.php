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
        Schema::create('edu_status', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_status')->primary();

            $table->string('nome', 120);
            $table->string('descricao', 250)->nullable();

            $table->boolean('acessa_vera_digital')->default(false);
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
        Schema::dropIfExists('edu_status');
    }
};
