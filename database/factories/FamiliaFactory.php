<?php

namespace Database\Factories;

use App\Models\Familia;
use App\Models\Pessoa;

/**
 * Cada linha liga um familiar (cod_pessoa) a um aluno (cod_pessoa_aluno), com um cod_familia próprio.
 *
 * @extends BaseFactory<Familia>
 */
class FamiliaFactory extends BaseFactory
{
    protected $model = Familia::class;

    public function definition(): array
    {
        return [
            'cod_familia' => fn () => $this->nextKey(),
            'cod_pessoa' => Pessoa::factory()->adulto(),
            'cod_pessoa_aluno' => Pessoa::factory()->aluno(10),
            'ativo' => true,
        ];
    }
}
