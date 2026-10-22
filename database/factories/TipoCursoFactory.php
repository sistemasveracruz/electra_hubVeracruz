<?php

namespace Database\Factories;

use App\Models\Escola;
use App\Models\TipoCurso;
use Database\Seeders\Support\Catalogo;

/**
 * @extends BaseFactory<TipoCurso>
 */
class TipoCursoFactory extends BaseFactory
{
    protected $model = TipoCurso::class;

    public function definition(): array
    {
        return [
            'cod_tipo_curso' => fn () => $this->nextKey(),
            'cod_escola' => Escola::factory(),
            'nome' => $this->faker->randomElement(array_column(Catalogo::CURSOS, 'nivel')),
            'ativo' => true,
        ];
    }
}
