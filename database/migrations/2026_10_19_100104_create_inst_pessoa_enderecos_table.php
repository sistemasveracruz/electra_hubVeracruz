<?php

use App\Enums\TipoEndereco;
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
        Schema::create('inst_pessoa_enderecos', function (Blueprint $table) {
            $table->bigIncrements('cod_pessoa_endereco');

            $table->foreignId('cod_pessoa')
                ->constrained('inst_pessoas', 'cod_pessoa');

            $table->enum('tipo_endereco', TipoEndereco::valores());
            $table->string('cep', 8)->nullable();
            $table->string('logradouro', 200)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento', 100)->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->char('uf', 2)->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('cod_pessoa', 'ix_pessoa_enderecos_cod_pessoa');
            $table->index(['cod_pessoa', 'tipo_endereco'], 'ix_pessoa_enderecos_cod_pessoa_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inst_pessoa_enderecos');
    }
};
