<?php

namespace Database\Seeders;

use App\Models\Escola;
use App\Models\UnidadeEscolar;
use Database\Seeders\Support\Catalogo;

/**
 * Instituto Vera Cruz (CNPJ fictício) e suas unidades.
 */
class InstitucionalSeeder extends DevSeeder
{
    protected function seed(): void
    {
        $escola = $this->firstOrCreate(Escola::class, ['cnpj' => Catalogo::escolaCnpj()], [
            'nome' => Catalogo::ESCOLA_NOME,
            'nome_fantasia' => Catalogo::ESCOLA_NOME_FANTASIA,
            'descricao' => 'Dados fictícios para o ambiente de desenvolvimento.',
            'created_at' => '2024-10-01 09:00:00',
            'updated_at' => '2024-10-01 09:00:00',
        ]);

        foreach (Catalogo::UNIDADES as $sigla => $unidade) {
            $this->firstOrCreate(UnidadeEscolar::class, [
                'cod_escola' => $escola->cod_escola,
                'sigla' => $sigla,
            ], [
                'nome' => $unidade['nome'],
                'descricao' => $unidade['descricao'],
                'created_at' => '2024-10-01 09:00:00',
                'updated_at' => '2024-10-01 09:00:00',
            ]);
        }
    }
}
