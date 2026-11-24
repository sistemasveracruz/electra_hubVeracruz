<?php

namespace App\Rm\Cargas;

use App\Models\Turma;
use App\Rm\Carga;

/**
 * STURMA -> edu_turmas. Curso, etapa e turno vêm da habilitação-filial da turma
 * (SHABILITACAOFILIAL). A sala fica nula: no RM ela é da turma/disciplina.
 */
class CargaTurmas extends Carga
{
    protected bool $incremental = true;

    public static function nome(): string
    {
        return 'turmas';
    }

    public function executar(): void
    {
        $query = $this->rm()->table('dbo.STURMA as T')
            ->join('dbo.SHABILITACAOFILIAL as H', function ($join) {
                $join->on('H.CODCOLIGADA', '=', 'T.CODCOLIGADA')
                    ->on('H.IDHABILITACAOFILIAL', '=', 'T.IDHABILITACAOFILIAL');
            })
            ->where('T.CODCOLIGADA', $this->coligada)
            ->select('T.CODFILIAL', 'T.IDPERLET', 'T.CODTURMA', 'T.NOME', 'T.NOMERED', 'T.TURMAENCERRADA', 'H.CODCURSO', 'H.CODHABILITACAO', 'H.CODTURNO')
            ->orderBy('T.IDPERLET')
            ->orderBy('T.CODTURMA');

        foreach ($this->alteradoDesde($query, 'T', 'H')->lazy(500) as $linha) {
            $chave = "{$this->coligada}|{$linha->IDPERLET}|{$linha->CODTURMA}";

            $referencias = [
                'cod_unidade_escolar' => $this->codHub("{$this->coligada}|{$linha->CODFILIAL}", 'unidades'),
                'cod_periodo_letivo' => $this->codHub("{$this->coligada}|{$linha->IDPERLET}", 'periodos'),
                'cod_curso' => $this->codHub("{$this->coligada}|{$linha->CODCURSO}", 'cursos'),
                'cod_etapa' => $this->codHub("{$this->coligada}|{$linha->CODCURSO}|{$linha->CODHABILITACAO}", 'etapas'),
                'cod_turno' => $this->codHub("{$this->coligada}|{$linha->CODTURNO}", 'turnos'),
            ];

            if ($faltando = array_keys($referencias, null, true)) {
                $this->ignorar($chave, 'sem '.implode(', ', $faltando).' no HUB');

                continue;
            }

            $turma = $this->localizar(Turma::class, $chave, fn () => new Turma);

            if (! $turma) {
                continue;
            }

            $this->preencher($turma, $referencias + [
                'codigo_turma' => $this->texto($linha->CODTURMA, 20),
                'nome' => $this->texto($linha->NOME, 120) ?? $linha->CODTURMA,
                'nome_reduzido' => $this->texto($linha->NOMERED, 12) ?? $this->texto($linha->CODTURMA, 12),
                // TURMAENCERRADA é S/N no RM; outro valor não altera o HUB.
                'ativo' => match ($linha->TURMAENCERRADA) {
                    'S' => false,
                    'N' => true,
                    default => null,
                },
            ]);

            $this->gravar($turma, $chave);
        }
    }
}
