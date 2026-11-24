<?php

namespace App\Rm\Cargas;

use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\RegistroAcademico;
use App\Models\RmChave;
use App\Rm\Carga;

/**
 * edu_alunos: um aluno por RA, apontando para a matrícula do período letivo mais recente.
 * Calculado só com o que as cargas ras e matriculas já gravaram no HUB; não lê o RM.
 */
class CargaAlunos extends Carga
{
    public static function nome(): string
    {
        return 'alunos';
    }

    public function executar(): void
    {
        $ras = RmChave::where('entidade', 'ras')
            ->where('chave_rm', 'like', "{$this->coligada}|%")
            ->lazyById(500, 'cod_rm_chave');

        foreach ($ras as $ra) {
            $registro = RegistroAcademico::find($ra->cod_hub);

            if (! $registro) {
                continue;
            }

            $codMatricula = Matricula::query()
                ->join('edu_turmas as t', 't.cod_turma', '=', 'edu_matriculas.cod_turma')
                ->join('edu_periodos_letivos as pl', 'pl.cod_periodo_letivo', '=', 't.cod_periodo_letivo')
                ->where('edu_matriculas.cod_registro_academico', $registro->cod_registro_academico)
                ->orderByDesc('pl.data_inicio')
                ->orderByDesc('edu_matriculas.cod_matricula')
                ->value('edu_matriculas.cod_matricula');

            // Sem matrícula no HUB não há aluno: edu_alunos.cod_matricula é obrigatório.
            if ($codMatricula === null) {
                continue;
            }

            $aluno = $this->localizar(Aluno::class, $ra->chave_rm, fn () => new Aluno);

            if (! $aluno) {
                continue;
            }

            $this->preencher($aluno, [
                'cod_pessoa' => $registro->cod_pessoa,
                'cod_registro_academico' => $registro->cod_registro_academico,
                'cod_matricula' => $codMatricula,
            ]);

            $this->gravar($aluno, $ra->chave_rm);
        }
    }
}
