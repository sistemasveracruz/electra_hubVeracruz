<?php

namespace Database\Factories;

use App\Models\Sala;
use App\Models\TipoSala;
use App\Models\UnidadeEscolar;

/**
 * @extends BaseFactory<Sala>
 */
class SalaFactory extends BaseFactory
{
    protected $model = Sala::class;

    public function definition(): array
    {
        return [
            'cod_unidade_escolar' => UnidadeEscolar::factory(),
            'cod_tipo_sala' => TipoSala::factory(),
            'nome' => 'Sala '.$this->faker->numberBetween(1, 30),
            'descricao' => null,
            'ativo' => true,
        ];
    }
}
