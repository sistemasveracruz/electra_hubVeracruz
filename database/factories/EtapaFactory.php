<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Etapa;

/**
 * @extends BaseFactory<Etapa>
 */
class EtapaFactory extends BaseFactory
{
    protected $model = Etapa::class;

    /**
     * Próximo cod_etapa por curso (a PK é composta: cod_curso + cod_etapa).
     *
     * @var array<int, int>
     */
    protected static array $nextEtapas = [];

    public function definition(): array
    {
        return [
            'cod_curso' => Curso::factory(),
            'cod_etapa' => fn (array $atributos) => $this->nextEtapa((int) $atributos['cod_curso']),
            'nome' => fn (array $atributos) => "{$atributos['cod_etapa']}º ano",
            'sigla' => fn (array $atributos) => "{$atributos['cod_etapa']}º",
            'ativo' => true,
        ];
    }

    protected function nextEtapa(int $codCurso): int
    {
        static::$nextEtapas[$codCurso] ??= (int) Etapa::query()
            ->withTrashed()
            ->where('cod_curso', $codCurso)
            ->max('cod_etapa');

        return ++static::$nextEtapas[$codCurso];
    }
}
