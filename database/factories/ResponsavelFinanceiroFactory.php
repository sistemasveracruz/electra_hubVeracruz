<?php

namespace Database\Factories;

use App\Models\Pessoa;
use App\Models\ResponsavelFinanceiro;

/**
 * @extends BaseFactory<ResponsavelFinanceiro>
 */
class ResponsavelFinanceiroFactory extends BaseFactory
{
    protected $model = ResponsavelFinanceiro::class;

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
