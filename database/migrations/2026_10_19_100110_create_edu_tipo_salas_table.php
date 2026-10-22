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
        Schema::create('edu_tipo_salas', function (Blueprint $table) {
            $table->unsignedBigInteger('cod_tipo_sala')->primary();

            $table->string('nome', 120);
            $table->boolean('permite_reserva')->default(false);

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
        Schema::dropIfExists('edu_tipo_salas');
    }
};
