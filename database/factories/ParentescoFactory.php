<?php

namespace Database\Factories;

use App\Models\Parentesco;
use Database\Seeders\Support\Catalogo;

/**
 * @extends BaseFactory<Parentesco>
 */
class ParentescoFactory extends BaseFactory
{
    protected $model = Parentesco::class;

    public function definition(): array
    {
        return [
            'descricao' => $this->faker->randomElement(Catalogo::PARENTESCOS),
            'ativo' => true,
        ];
    }
}
