<?php

namespace Database\Factories;

use App\Models\Disciplina;
use App\Models\Turma;
use App\Models\TurmaDisciplina;

/**
 * @extends BaseFactory<TurmaDisciplina>
 */
class TurmaDisciplinaFactory extends BaseFactory
{
    protected $model = TurmaDisciplina::class;

    public function definition(): array
    {
        return [
            'cod_turma_disciplina' => fn () => $this->nextKey(),
            'cod_turma' => Turma::factory(),
            'cod_disciplina' => Disciplina::factory(),
            'ativo' => true,
        ];
    }
}
