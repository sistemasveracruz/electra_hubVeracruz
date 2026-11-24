<?php

namespace Database\Factories;

use App\Models\Disciplina;
use Database\Seeders\Support\Catalogo;

/**
 * @extends BaseFactory<Disciplina>
 */
class DisciplinaFactory extends BaseFactory
{
    protected $model = Disciplina::class;

    public function definition(): array
    {
        $sigla = $this->faker->randomElement(array_keys(Catalogo::DISCIPLINAS));

        return [
            'nome' => Catalogo::DISCIPLINAS[$sigla],
            'sigla' => $sigla,
            'observacoes' => null,
            'ativo' => true,
        ];
    }

    public function inativa(): static
    {
        return $this->state(['ativo' => false]);
    }
}
