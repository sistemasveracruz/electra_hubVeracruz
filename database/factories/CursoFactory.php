<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Escola;
use Database\Seeders\Support\Catalogo;

/**
 * @extends BaseFactory<Curso>
 */
class CursoFactory extends BaseFactory
{
    protected $model = Curso::class;

    public function definition(): array
    {
        $sigla = $this->faker->randomElement(array_keys(Catalogo::CURSOS));

        return [
            'cod_curso' => fn () => $this->nextKey(),
            'cod_escola' => Escola::factory(),
            'nome' => Catalogo::CURSOS[$sigla]['nome'],
            'sigla' => $sigla,
            'ativo' => true,
        ];
    }
}
