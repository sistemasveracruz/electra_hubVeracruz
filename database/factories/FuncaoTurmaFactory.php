<?php

namespace Database\Factories;

use App\Models\FuncaoTurma;
use Illuminate\Support\Str;

/**
 * @extends BaseFactory<FuncaoTurma>
 */
class FuncaoTurmaFactory extends BaseFactory
{
    protected $model = FuncaoTurma::class;

    public function definition(): array
    {
        return [
            'codigo' => Str::upper($this->faker->unique()->bothify('FUNCAO_###')),
            'descricao' => Str::ucfirst($this->faker->words(3, true)),
        ];
    }
}
