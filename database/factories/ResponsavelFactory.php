<?php

namespace Database\Factories;

use App\Models\Aluno;
use App\Models\Responsavel;
use App\Models\ResponsavelFinanceiro;
use App\Models\ResponsavelPedagogico;

/**
 * Liga um aluno aos seus responsáveis pedagógico e financeiro (PK composta).
 * Por padrão, os responsáveis são criados para a mesma pessoa do aluno.
 *
 * @extends BaseFactory<Responsavel>
 */
class ResponsavelFactory extends BaseFactory
{
    protected $model = Responsavel::class;

    public function definition(): array
    {
        return [
            'cod_aluno' => Aluno::factory(),
            'cod_pessoa_aluno' => fn (array $atributos) => Aluno::query()->findOrFail($atributos['cod_aluno'])->cod_pessoa,
            'cod_responsavel_pedagogico' => fn (array $atributos) => ResponsavelPedagogico::factory()->create([
                'cod_pessoa_aluno' => $atributos['cod_pessoa_aluno'],
            ])->cod_responsavel_pedagogico,
            'cod_responsavel_financeiro' => fn (array $atributos) => ResponsavelFinanceiro::factory()->create([
                'cod_pessoa_aluno' => $atributos['cod_pessoa_aluno'],
            ])->cod_responsavel_financeiro,
        ];
    }
}
