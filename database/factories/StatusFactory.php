<?php

namespace Database\Factories;

use App\Models\Status;
use Database\Seeders\Support\Catalogo;

/**
 * @extends BaseFactory<Status>
 */
class StatusFactory extends BaseFactory
{
    protected $model = Status::class;

    public function definition(): array
    {
        return [
            'cod_status' => fn () => $this->nextKey(),
            'nome' => $this->faker->randomElement(array_keys(Catalogo::STATUS)),
            'descricao' => fn (array $atributos) => Catalogo::STATUS[$atributos['nome']] ?? null,
            'ativo' => true,
        ];
    }
}
