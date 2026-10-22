<?php

namespace Database\Factories;

use App\Models\Pessoa;
use App\Models\ResponsavelPedagogico;

/**
 * @extends BaseFactory<ResponsavelPedagogico>
 */
class ResponsavelPedagogicoFactory extends BaseFactory
{
    protected $model = ResponsavelPedagogico::class;

    public function definition(): array
    {
        return [
            'cod_pessoa_aluno' => Pessoa::factory()->aluno(10),
            'cod_pessoa_responsavel' => Pessoa::factory()->adulto(),
            'cod_parentesco_responsavel' => null,
            'ativo' => true,
        ];
    }

    public function comParentesco(string $descricao): static
    {
        return $this->state(fn () => ['cod_parentesco_responsavel' => $this->parentescoId($descricao)]);
    }
}
