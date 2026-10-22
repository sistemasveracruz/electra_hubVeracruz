<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Etapa;
use App\Models\GradeCurricular;
use Database\Seeders\Support\Catalogo;
use Illuminate\Support\Str;

/**
 * @extends BaseFactory<GradeCurricular>
 */
class GradeCurricularFactory extends BaseFactory
{
    protected $model = GradeCurricular::class;

    public function definition(): array
    {
        [$inicio, $fim] = Catalogo::PERIODOS[Catalogo::ANO_LETIVO_ATUAL];

        return [
            'cod_grade' => Str::upper($this->faker->unique()->bothify('GRD-####-'.Catalogo::ANO_LETIVO_ATUAL)),
            'cod_curso' => Curso::factory(),
            'cod_etapa' => fn (array $atributos) => Etapa::factory()->create([
                'cod_curso' => $atributos['cod_curso'],
            ])->cod_etapa,
            'descricao' => fn (array $atributos) => "Grade curricular {$atributos['cod_grade']}",
            'data_inicio' => $inicio,
            'data_fim' => $fim,
            'carga_horaria' => $this->faker->randomElement([800, 1000, 1200]),
            'ativo' => true,
        ];
    }
}
