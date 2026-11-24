<?php

namespace App\Rm\Cargas;

use App\Models\Enturma;
use App\Models\Matricula;
use App\Rm\Carga;

/**
 * edu_enturmas: uma por matrícula, na turma da matrícula (SMATRICPL.CODTURMA),
 * com o número de chamada (SMATRICPL.NUMALUNO).
 */
class CargaEnturmas extends Carga
{
    protected bool $incremental = true;

    public static function nome(): string
    {
        return 'enturmas';
    }

    public function executar(): void
    {
        $query = $this->rm()->table('dbo.SMATRICPL as M')
            ->where('M.CODCOLIGADA', $this->coligada)
            ->whereNotNull('M.CODTURMA')
            ->select('M.IDPERLET', 'M.RA', 'M.IDHABILITACAOFILIAL', 'M.NUMALUNO')
            // Ordem única (PK de SMATRICPL): o lazy() pagina por OFFSET.
            ->orderBy('M.IDPERLET')
            ->orderBy('M.RA')
            ->orderBy('M.IDHABILITACAOFILIAL');

        foreach ($this->alteradoDesde($query, 'M')->lazy(500) as $linha) {
            $ra = trim($linha->RA);
            $chave = "{$this->coligada}|{$linha->IDPERLET}|{$ra}|{$linha->IDHABILITACAOFILIAL}";

            // Matrícula ou aluno ausentes já foram listados pelas cargas anteriores.
            $codMatricula = $this->codHub($chave, 'matriculas');
            $codAluno = $this->codHub("{$this->coligada}|{$ra}", 'alunos');
            $matricula = $codMatricula === null ? null : Matricula::find($codMatricula);

            if (! $matricula || $codAluno === null) {
                continue;
            }

            $enturma = $this->localizar(Enturma::class, $chave, fn () => new Enturma);

            if (! $enturma) {
                continue;
            }

            $this->preencher($enturma, [
                'cod_aluno' => $codAluno,
                'cod_matricula' => $matricula->cod_matricula,
                'cod_turma' => $matricula->cod_turma,
                'cod_status' => $matricula->cod_status,
                'numero_chamada' => $linha->NUMALUNO === null ? null : (string) $linha->NUMALUNO,
            ]);

            $this->gravar($enturma, $chave);
        }
    }
}
