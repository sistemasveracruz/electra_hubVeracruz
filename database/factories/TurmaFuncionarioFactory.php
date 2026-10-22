<?php

namespace Database\Factories;

use App\Models\FuncaoTurma;
use App\Models\Funcionario;
use App\Models\Turma;
use App\Models\TurmaFuncionario;

/**
 * Por padrão, a atuação começa no início do período letivo da turma.
 *
 * @extends BaseFactory<TurmaFuncionario>
 */
class TurmaFuncionarioFactory extends BaseFactory
{
    protected $model = TurmaFuncionario::class;

    public function definition(): array
    {
        return [
            'cod_turma' => Turma::factory(),
            'cod_funcionario' => Funcionario::factory()->professor(),
            'cod_funcao_turma' => FuncaoTurma::factory(),
            'data_inicio' => fn (array $atributos) => Turma::query()
                ->findOrFail($atributos['cod_turma'])->periodoLetivo->data_inicio,
            'data_fim' => null,
            'ativo' => true,
        ];
    }
}
