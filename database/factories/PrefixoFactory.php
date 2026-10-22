<?php

namespace Database\Factories;

use App\Models\Prefixo;
use Illuminate\Support\Str;

/**
 * @extends BaseFactory<Prefixo>
 */
class PrefixoFactory extends BaseFactory
{
    protected $model = Prefixo::class;

    public function definition(): array
    {
        return [
            'cod_prefixo' => fn () => $this->nextKey(),
            'sigla' => Str::upper($this->faker->unique()->lexify('????')),
            'descricao' => Str::ucfirst($this->faker->word()),
        ];
    }
}
