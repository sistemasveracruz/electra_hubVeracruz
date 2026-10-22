<?php

namespace Database\Factories;

use App\Models\Escola;

/**
 * @extends BaseFactory<Escola>
 */
class EscolaFactory extends BaseFactory
{
    protected $model = Escola::class;

    public function definition(): array
    {
        $sobrenome = $this->faker->lastName();

        return [
            'cod_escola' => fn () => $this->nextKey(),
            'nome' => "Colégio {$sobrenome}",
            'nome_fantasia' => $sobrenome,
            'cnpj' => $this->faker->unique()->cnpj(false),
            'descricao' => null,
            'ativo' => true,
        ];
    }

    public function inativa(): static
    {
        return $this->state(['ativo' => false]);
    }
}
