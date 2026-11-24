<?php

namespace App\Rm\Cargas;

use App\Models\Matricula;
use App\Rm\Carga;

/**
 * SMATRICPL -> edu_matriculas (matrícula do aluno no período letivo).
 * Curso e etapa vêm da habilitação-filial da matrícula; a turma é obrigatória no HUB.
 */
class CargaMatriculas extends Carga
{
    protected bool $incremental = true;

    public static function nome(): string
    {
        return 'matriculas';
    }

    public function executar(): void
    {
        $codEscola = $this->codEscola();

        $query = $this->rm()->table('dbo.SMATRICPL as M')
            ->join('dbo.SALUNO as A', function ($join) {
                $join->on('A.CODCOLIGADA', '=', 'M.CODCOLIGADA')->on('A.RA', '=', 'M.RA');
            })
            ->join('dbo.SHABILITACAOFILIAL as H', function ($join) {
                $join->on('H.CODCOLIGADA', '=', 'M.CODCOLIGADA')
                    ->on('H.IDHABILITACAOFILIAL', '=', 'M.IDHABILITACAOFILIAL');
            })
            ->where('M.CODCOLIGADA', $this->coligada)
            ->select('M.IDPERLET', 'M.RA', 'M.IDHABILITACAOFILIAL', 'M.CODFILIAL', 'M.CODTURMA', 'M.CODSTATUS',
                'A.CODPESSOA', 'H.CODCURSO', 'H.CODHABILITACAO')
            // Ordem única (PK de SMATRICPL): o lazy() pagina por OFFSET.
            ->orderBy('M.IDPERLET')
            ->orderBy('M.RA')
            ->orderBy('M.IDHABILITACAOFILIAL');

        foreach ($this->alteradoDesde($query, 'M')->lazy(500) as $linha) {
            $ra = trim($linha->RA);
            $chave = "{$this->coligada}|{$linha->IDPERLET}|{$ra}|{$linha->IDHABILITACAOFILIAL}";

            if (! $linha->CODTURMA) {
                $this->ignorar("matrícula {$chave}", 'sem turma no RM (edu_matriculas.cod_turma é obrigatório)');

                continue;
            }

            $referencias = [
                'cod_pessoa' => $this->codHub((string) $linha->CODPESSOA, 'pessoas'),
                'cod_registro_academico' => $this->codHub("{$this->coligada}|{$ra}", 'ras'),
                'cod_unidade_escolar' => $this->codHub("{$this->coligada}|{$linha->CODFILIAL}", 'unidades'),
                'cod_curso' => $this->codHub("{$this->coligada}|{$linha->CODCURSO}", 'cursos'),
                'cod_etapa' => $this->codHub("{$this->coligada}|{$linha->CODCURSO}|{$linha->CODHABILITACAO}", 'etapas'),
                'cod_turma' => $this->codHub("{$this->coligada}|{$linha->IDPERLET}|{$linha->CODTURMA}", 'turmas'),
                'cod_status' => $this->codHub("{$this->coligada}|{$linha->CODSTATUS}", 'status'),
            ];

            if ($faltando = array_keys($referencias, null, true)) {
                $this->ignorar("matrícula {$chave}", 'sem '.implode(', ', $faltando).' no HUB');

                continue;
            }

            $matricula = $this->localizar(Matricula::class, $chave, fn () => new Matricula);

            if (! $matricula) {
                continue;
            }

            $this->preencher($matricula, $referencias + ['cod_escola' => $codEscola]);
            $this->gravar($matricula, $chave);
        }
    }
}
