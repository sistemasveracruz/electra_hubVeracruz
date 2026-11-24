<?php

namespace Database\Factories;

use App\Models\Aluno;
use App\Models\Enturma;
use App\Models\Matricula;

/**
 * Turma e status são os da matrícula; o aluno é o da pessoa matriculada
 * (criado a partir da matrícula se ainda não existir).
 *
 * @extends BaseFactory<Enturma>
 */
class EnturmaFactory extends BaseFactory
{
    protected $model = Enturma::class;

    public function definition(): array
    {
        return [
            'cod_matricula' => Matricula::factory(),
            'cod_aluno' => fn (array $atributos) => $this->aluno($this->matricula($atributos)),
            'cod_turma' => fn (array $atributos) => $this->matricula($atributos)->cod_turma,
            'cod_status' => fn (array $atributos) => $this->matricula($atributos)->cod_status,
            'numero_chamada' => sprintf('%02d', $this->faker->numberBetween(1, 35)),
            'ativo' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function matricula(array $atributos): Matricula
    {
        return Matricula::query()->findOrFail($atributos['cod_matricula']);
    }

    private function aluno(Matricula $matricula): int
    {
        return Aluno::query()->where('cod_pessoa', $matricula->cod_pessoa)->value('cod_aluno')
            ?? Aluno::factory()->create(['cod_matricula' => $matricula->cod_matricula])->cod_aluno;
    }
}
