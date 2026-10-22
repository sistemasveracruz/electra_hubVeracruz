<?php

namespace Database\Factories;

use App\Models\PeriodoLetivo;
use App\Models\TipoCurso;
use App\Models\UnidadeEscolar;
use Database\Seeders\Support\Catalogo;

/**
 * @extends BaseFactory<PeriodoLetivo>
 */
class PeriodoLetivoFactory extends BaseFactory
{
    protected $model = PeriodoLetivo::class;

    public function definition(): array
    {
        [$inicio, $fim] = Catalogo::PERIODOS[Catalogo::ANO_LETIVO_ATUAL];

        return [
            'cod_periodo_letivo' => fn () => $this->nextKey(),
            'cod_unidade_escolar' => UnidadeEscolar::factory(),
            // O tipo de curso pertence à mesma escola da unidade.
            'cod_tipo_curso' => fn (array $atributos) => TipoCurso::factory()->create([
                'cod_escola' => UnidadeEscolar::query()->findOrFail($atributos['cod_unidade_escolar'])->cod_escola,
            ])->cod_tipo_curso,
            'cod_periodo_letivo_anterior' => null,
            'data_inicio' => $inicio,
            'data_fim' => $fim,
            'data_estimada' => null,
            'ativo' => true,
        ];
    }

    /**
     * Período do ano letivo informado (datas do Catalogo, quando houver).
     */
    public function anoLetivo(int $ano): static
    {
        [$inicio, $fim] = Catalogo::PERIODOS[$ano] ?? ["{$ano}-02-01 00:00:00", "{$ano}-12-15 00:00:00"];

        return $this->state(['data_inicio' => $inicio, 'data_fim' => $fim]);
    }

    /**
     * Período que sucede o informado, na mesma unidade e tipo de curso.
     */
    public function sucessorDe(PeriodoLetivo $anterior): static
    {
        return $this->anoLetivo($anterior->data_inicio->year + 1)->state([
            'cod_unidade_escolar' => $anterior->cod_unidade_escolar,
            'cod_tipo_curso' => $anterior->cod_tipo_curso,
            'cod_periodo_letivo_anterior' => $anterior->cod_periodo_letivo,
        ]);
    }
}
