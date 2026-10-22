<?php

namespace Database\Factories;

use App\Models\Aluno;
use App\Models\Matricula;

/**
 * Pessoa e registro acadêmico são derivados da matrícula vigente do aluno.
 *
 * @extends BaseFactory<Aluno>
 */
class AlunoFactory extends BaseFactory
{
    protected $model = Aluno::class;

    public function definition(): array
    {
        return [
            'cod_matricula' => Matricula::factory(),
            'cod_pessoa' => fn (array $atributos) => $this->matricula($atributos)->cod_pessoa,
            'cod_registro_academico' => fn (array $atributos) => $this->matricula($atributos)->cod_registro_academico,
            'ativo' => true,
        ];
    }

    public function inativo(): static
    {
        return $this->state(['ativo' => false]);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function matricula(array $atributos): Matricula
    {
        return Matricula::query()->findOrFail($atributos['cod_matricula']);
    }
}
