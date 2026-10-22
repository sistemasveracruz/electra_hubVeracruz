<?php

namespace Database\Factories;

use App\Models\Turno;
use Database\Seeders\Support\Catalogo;

/**
 * @extends BaseFactory<Turno>
 */
class TurnoFactory extends BaseFactory
{
    protected $model = Turno::class;

    public function definition(): array
    {
        $sigla = $this->faker->randomElement(array_keys(Catalogo::TURNOS));

        return [
            'cod_turno' => fn () => $this->nextKey(),
            'nome' => Catalogo::TURNOS[$sigla],
            'sigla' => $sigla,
        ];
    }
}
