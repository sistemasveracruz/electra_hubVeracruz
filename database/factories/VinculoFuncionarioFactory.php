<?php

namespace Database\Factories;

use App\Models\Funcionario;
use App\Models\UnidadeEscolar;
use App\Models\VinculoFuncionario;

/**
 * A escola do vínculo é a da unidade escolar.
 *
 * @extends BaseFactory<VinculoFuncionario>
 */
class VinculoFuncionarioFactory extends BaseFactory
{
    protected $model = VinculoFuncionario::class;

    public function definition(): array
    {
        return [
            'cod_funcionario' => Funcionario::factory(),
            'cod_unidade_escolar' => UnidadeEscolar::factory(),
            'cod_escola' => fn (array $atributos) => UnidadeEscolar::query()
                ->findOrFail($atributos['cod_unidade_escolar'])->cod_escola,
            'cargo' => 'Professor(a)',
            'setor' => 'Pedagógico',
            'tipo_vinculo' => 'CLT',
            'data_admissao' => $this->faker->dateTimeBetween('-12 years', '-2 months'),
            'data_desligamento' => null,
            'ativo' => true,
        ];
    }

    public function desligado(string $dataDesligamento = '2025-12-19 00:00:00'): static
    {
        return $this->state([
            'data_desligamento' => $dataDesligamento,
            'ativo' => false,
        ]);
    }
}
