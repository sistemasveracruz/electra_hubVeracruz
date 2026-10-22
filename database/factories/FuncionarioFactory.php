<?php

namespace Database\Factories;

use App\Models\Funcionario;
use App\Models\Pessoa;

/**
 * @extends BaseFactory<Funcionario>
 */
class FuncionarioFactory extends BaseFactory
{
    protected $model = Funcionario::class;

    public function definition(): array
    {
        return [
            'cod_funcionario' => fn () => $this->nextKey(),
            'cod_pessoa' => Pessoa::factory()->adulto(23, 62),
            'tipo_cadastro' => 'PESSOA',
            'professor_cadastro' => false,
            'ativo' => true,
        ];
    }

    public function professor(): static
    {
        return $this->state(['professor_cadastro' => true]);
    }

    public function administrativo(): static
    {
        return $this->state(['professor_cadastro' => false]);
    }

    public function inativo(): static
    {
        return $this->state(['ativo' => false]);
    }
}
