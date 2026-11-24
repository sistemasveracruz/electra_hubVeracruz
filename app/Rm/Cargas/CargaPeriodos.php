<?php

namespace App\Rm\Cargas;

use App\Models\PeriodoLetivo;
use App\Rm\Carga;

/** SPLETIVO -> edu_periodos_letivos (um período por filial e tipo de curso, como no RM). */
class CargaPeriodos extends Carga
{
    public static function nome(): string
    {
        return 'periodos';
    }

    public function executar(): void
    {
        // Em ordem de IDPERLET, para o período anterior normalmente já estar no de-para.
        $linhas = $this->rm()->table('dbo.SPLETIVO')
            ->where('CODCOLIGADA', $this->coligada)
            ->orderBy('IDPERLET')
            ->get(['IDPERLET', 'CODPERLET', 'CODFILIAL', 'CODTIPOCURSO', 'IDPERLETANT', 'DTINICIO', 'DTFIM', 'DTPREVISTA', 'ENCERRADO']);

        foreach ($linhas as $linha) {
            $chave = "{$this->coligada}|{$linha->IDPERLET}";
            $codUnidade = $this->codHub("{$this->coligada}|{$linha->CODFILIAL}", 'unidades');
            $codTipo = $this->codHub("{$this->coligada}|{$linha->CODTIPOCURSO}", 'tipos-curso');

            if ($codUnidade === null || $codTipo === null) {
                $this->ignorar("{$chave} ({$linha->CODPERLET})", "filial {$linha->CODFILIAL} ou tipo de curso {$linha->CODTIPOCURSO} não carregado");

                continue;
            }

            $periodo = $this->localizar(PeriodoLetivo::class, $chave, fn () => new PeriodoLetivo([
                'cod_periodo_letivo' => $this->novoCodigo(PeriodoLetivo::class, 'cod_periodo_letivo', $linha->IDPERLET),
            ]));

            if (! $periodo) {
                continue;
            }

            $this->preencher($periodo, [
                'cod_unidade_escolar' => $codUnidade,
                'cod_tipo_curso' => $codTipo,
                'cod_periodo_letivo_anterior' => $linha->IDPERLETANT
                    ? $this->codHub("{$this->coligada}|{$linha->IDPERLETANT}")
                    : null,
                'data_inicio' => $linha->DTINICIO,
                'data_fim' => $linha->DTFIM,
                'data_estimada' => $linha->DTPREVISTA,
                // ENCERRADO é S/N no RM; outro valor não altera o HUB.
                'ativo' => match ($linha->ENCERRADO) {
                    'S' => false,
                    'N' => true,
                    default => null,
                },
            ]);

            $this->gravar($periodo, $chave);
        }
    }
}
