<?php

namespace Database\Factories;

use App\Models\Funcionario;
use App\Models\TurmaDisciplina;
use App\Models\TurmaDisciplinaFuncionario;

/**
 * Por padrão, o professor é titular desde o início do período letivo da turma.
 *
 * @extends BaseFactory<TurmaDisciplinaFuncionario>
 */
class TurmaDisciplinaFuncionarioFactory extends BaseFactory
{
    protected $model = TurmaDisciplinaFuncionario::class;

    public function definition(): array
    {
        return [
            'cod_turma_disciplina_funcionario' => fn () => $this->nextKey(),
            'cod_turma_disciplina' => TurmaDisciplina::factory(),
            'cod_funcionario' => Funcionario::factory()->professor(),
            'tipo_atribuicao' => 'TITULAR',
            'codigo_papel_origem' => null,
            'data_inicio' => fn (array $atributos) => TurmaDisciplina::query()
                ->findOrFail($atributos['cod_turma_disciplina'])->turma->periodoLetivo->data_inicio,
            'data_fim' => null,
            'ativo' => true,
        ];
    }

    /**
     * Substituição temporária do titular no intervalo informado.
     */
    public function substituto(string $inicio, string $fim): static
    {
        return $this->state([
            'tipo_atribuicao' => 'SUBSTITUTO',
            'data_inicio' => $inicio,
            'data_fim' => $fim,
        ]);
    }
}
