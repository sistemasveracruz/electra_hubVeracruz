<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Etapa;
use App\Models\PeriodoLetivo;
use App\Models\Sala;
use App\Models\Turma;
use App\Models\Turno;
use App\Models\UnidadeEscolar;
use Illuminate\Support\Str;

/**
 * Por padrão, período, curso, etapa e sala são criados de forma coerente com a unidade
 * (mesma unidade/escola, etapa pertencente ao curso). Qualquer um pode ser informado.
 *
 * @extends BaseFactory<Turma>
 */
class TurmaFactory extends BaseFactory
{
    protected $model = Turma::class;

    public function definition(): array
    {
        return [
            'codigo_turma' => Str::upper($this->faker->unique()->bothify('T-####')),
            'cod_unidade_escolar' => UnidadeEscolar::factory(),
            'cod_periodo_letivo' => fn (array $atributos) => PeriodoLetivo::factory()->create([
                'cod_unidade_escolar' => $atributos['cod_unidade_escolar'],
            ])->cod_periodo_letivo,
            'cod_curso' => fn (array $atributos) => Curso::factory()->create([
                'cod_escola' => $this->escolaDaUnidade($atributos['cod_unidade_escolar']),
            ])->cod_curso,
            'cod_etapa' => fn (array $atributos) => Etapa::factory()->create([
                'cod_curso' => $atributos['cod_curso'],
            ])->cod_etapa,
            'cod_sala' => fn (array $atributos) => Sala::factory()->create([
                'cod_unidade_escolar' => $atributos['cod_unidade_escolar'],
            ])->cod_sala,
            'cod_turno' => Turno::factory(),
            'nome' => fn (array $atributos) => "Turma {$atributos['codigo_turma']}",
            'nome_reduzido' => fn (array $atributos) => Str::limit($atributos['codigo_turma'], 12, ''),
            'ativo' => true,
        ];
    }

    public function inativa(): static
    {
        return $this->state(['ativo' => false]);
    }

    private function escolaDaUnidade(int $codUnidade): int
    {
        return UnidadeEscolar::query()->findOrFail($codUnidade)->cod_escola;
    }
}
