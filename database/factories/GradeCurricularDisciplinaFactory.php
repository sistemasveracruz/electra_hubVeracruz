<?php

namespace Database\Factories;

use App\Models\Disciplina;
use App\Models\GradeCurricular;
use App\Models\GradeCurricularDisciplina;
use Database\Seeders\Support\Catalogo;

/**
 * Curso e etapa são os da grade curricular; a carga horária é aulas semanais × semanas.
 *
 * @extends BaseFactory<GradeCurricularDisciplina>
 */
class GradeCurricularDisciplinaFactory extends BaseFactory
{
    protected $model = GradeCurricularDisciplina::class;

    public function definition(): array
    {
        return [
            'cod_grade_disciplina' => fn () => $this->nextKey(),
            'cod_grade_curricular' => GradeCurricular::factory(),
            'cod_curso' => fn (array $atributos) => $this->grade($atributos)->cod_curso,
            'cod_etapa' => fn (array $atributos) => $this->grade($atributos)->cod_etapa,
            'cod_disciplina' => Disciplina::factory(),
            'cod_turno' => null,
            'periodo_grade' => 'ANUAL',
            'aulas_semanais' => $this->faker->numberBetween(1, 5),
            'quantidade_semanas' => Catalogo::SEMANAS_LETIVAS,
            'carga_horaria' => fn (array $atributos) => $atributos['aulas_semanais'] * $atributos['quantidade_semanas'],
            'obrigatoria' => true,
        ];
    }

    public function optativa(): static
    {
        return $this->state(['obrigatoria' => false]);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function grade(array $atributos): GradeCurricular
    {
        return GradeCurricular::query()->findOrFail($atributos['cod_grade_curricular']);
    }
}
