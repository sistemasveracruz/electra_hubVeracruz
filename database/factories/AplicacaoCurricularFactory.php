<?php

namespace Database\Factories;

use App\Models\AplicacaoCurricular;
use App\Models\GradeCurricular;
use App\Models\UnidadeEscolar;

/**
 * Curso, etapa e vigência são os da grade; a unidade pertence à escola do curso.
 *
 * @extends BaseFactory<AplicacaoCurricular>
 */
class AplicacaoCurricularFactory extends BaseFactory
{
    protected $model = AplicacaoCurricular::class;

    public function definition(): array
    {
        return [
            'cod_aplicacao_curricular' => fn () => $this->nextKey(),
            'cod_grade_curricular' => GradeCurricular::factory(),
            'cod_unidade_escolar' => fn (array $atributos) => UnidadeEscolar::factory()->create([
                'cod_escola' => $this->grade($atributos)->curso->cod_escola,
            ])->cod_unidade_escolar,
            'cod_curso' => fn (array $atributos) => $this->grade($atributos)->cod_curso,
            'cod_etapa' => fn (array $atributos) => $this->grade($atributos)->cod_etapa,
            'cod_turno' => null,
            'regime_grade' => 'ANUAL',
            'periodo_grade' => 'ANUAL',
            'data_inicio' => fn (array $atributos) => $this->grade($atributos)->data_inicio,
            'data_fim' => fn (array $atributos) => $this->grade($atributos)->data_fim,
        ];
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function grade(array $atributos): GradeCurricular
    {
        return GradeCurricular::query()->findOrFail($atributos['cod_grade_curricular']);
    }
}
