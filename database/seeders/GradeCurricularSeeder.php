<?php

namespace Database\Seeders;

use App\Models\AplicacaoCurricular;
use App\Models\GradeCurricular;
use App\Models\GradeCurricularDisciplina;
use Database\Seeders\Support\Catalogo;

/**
 * Grades curriculares de 2026 para as etapas que têm turma (Fundamental e Médio),
 * suas disciplinas e a aplicação de cada grade na unidade e nos turnos em que é ofertada.
 */
class GradeCurricularSeeder extends DevSeeder
{
    protected function seed(): void
    {
        $ano = Catalogo::ANO_LETIVO_ATUAL;
        [$inicio, $fim] = Catalogo::PERIODOS[$ano];
        $semanas = Catalogo::SEMANAS_LETIVAS;

        foreach (Catalogo::DISCIPLINAS_POR_CURSO as $siglaCurso => $disciplinas) {
            $curso = $this->curso($siglaCurso);
            $siglaUnidade = Catalogo::unidadeDoCurso($siglaCurso);
            $optativas = Catalogo::DISCIPLINAS_OPTATIVAS[$siglaCurso] ?? [];

            // Turnos em que cada etapa tem turma no ano: cod_etapa => [siglas de turno].
            $turnosPorEtapa = [];
            foreach (Catalogo::TURMAS[$ano][$siglaCurso] as [, $codEtapa, $siglaTurno]) {
                $turnosPorEtapa[$codEtapa][$siglaTurno] = $siglaTurno;
            }

            foreach ($turnosPorEtapa as $codEtapa => $turnos) {
                $nomeEtapa = Catalogo::CURSOS[$siglaCurso]['etapas'][$codEtapa][0];

                $grade = $this->firstOrCreate(GradeCurricular::class, [
                    'cod_grade' => "{$siglaCurso}-{$codEtapa}-{$ano}",
                ], [
                    'cod_curso' => $curso->cod_curso,
                    'cod_etapa' => $codEtapa,
                    'descricao' => "Grade curricular {$ano} – {$curso->nome} – {$nomeEtapa}",
                    'data_inicio' => $inicio,
                    'data_fim' => $fim,
                    'carga_horaria' => array_sum($disciplinas) * $semanas,
                ]);

                foreach ($disciplinas as $siglaDisciplina => $aulasSemanais) {
                    $this->firstOrCreate(GradeCurricularDisciplina::class, [
                        'cod_grade_curricular' => $grade->cod_grade_curricular,
                        'cod_disciplina' => $this->disciplina($siglaDisciplina)->cod_disciplina,
                    ], [
                        'cod_curso' => $curso->cod_curso,
                        'cod_etapa' => $codEtapa,
                        'cod_turno' => null,
                        'periodo_grade' => 'ANUAL',
                        'aulas_semanais' => $aulasSemanais,
                        'quantidade_semanas' => $semanas,
                        'carga_horaria' => $aulasSemanais * $semanas,
                        'obrigatoria' => ! in_array($siglaDisciplina, $optativas, true),
                    ]);
                }

                foreach ($turnos as $siglaTurno) {
                    $this->firstOrCreate(AplicacaoCurricular::class, [
                        'cod_grade_curricular' => $grade->cod_grade_curricular,
                        'cod_unidade_escolar' => $this->unidade($siglaUnidade)->cod_unidade_escolar,
                        'cod_turno' => $this->turno($siglaTurno)->cod_turno,
                    ], [
                        'cod_curso' => $curso->cod_curso,
                        'cod_etapa' => $codEtapa,
                        'regime_grade' => 'ANUAL',
                        'periodo_grade' => 'ANUAL',
                        'data_inicio' => $inicio,
                        'data_fim' => $fim,
                    ]);
                }
            }
        }
    }
}
