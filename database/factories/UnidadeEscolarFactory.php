<?php

namespace Database\Factories;

use App\Models\Escola;
use App\Models\UnidadeEscolar;
use Illuminate\Support\Str;

/**
 * @extends BaseFactory<UnidadeEscolar>
 */
class UnidadeEscolarFactory extends BaseFactory
{
    protected $model = UnidadeEscolar::class;

    public function definition(): array
    {
        $nome = $this->faker->unique()->lastName();

        return [
            'cod_unidade_escolar' => fn () => $this->nextKey(),
            'cod_escola' => Escola::factory(),
            'nome' => $nome,
            'sigla' => Str::upper(Str::limit(Str::ascii($nome), 20, '')),
            'descricao' => null,
            'ativo' => true,
        ];
    }

    public function inativa(): static
    {
        return $this->state(['ativo' => false]);
    }
}
