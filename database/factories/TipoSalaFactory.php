<?php

namespace Database\Factories;

use App\Models\TipoSala;
use Database\Seeders\Support\Catalogo;

/**
 * @extends BaseFactory<TipoSala>
 */
class TipoSalaFactory extends BaseFactory
{
    protected $model = TipoSala::class;

    public function definition(): array
    {
        $nome = $this->faker->randomElement(array_keys(Catalogo::TIPOS_SALA));

        return [
            'cod_tipo_sala' => fn () => $this->nextKey(),
            'nome' => $nome,
            'permite_reserva' => Catalogo::TIPOS_SALA[$nome],
            'ativo' => true,
        ];
    }

    public function reservavel(): static
    {
        return $this->state(['permite_reserva' => true]);
    }
}
