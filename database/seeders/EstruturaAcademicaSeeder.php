<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Etapa;
use App\Models\PeriodoLetivo;
use App\Models\Sala;
use App\Models\TipoCurso;
use App\Models\Turma;
use App\Models\TurmaDisciplina;
use Carbon\CarbonImmutable;
use Database\Seeders\Support\Catalogo;

/**
 * Níveis de ensino (tipos de curso), cursos, etapas, salas, períodos letivos,
 * turmas de 2025 (histórico) e 2026, e as disciplinas das turmas de 2026.
 */
class EstruturaAcademicaSeeder extends DevSeeder
{
    protected function seed(): void
    {
        $this->cursosEEtapas();
        $this->salas();
        $this->periodosLetivos();
        $this->turmas();
        $this->disciplinasDasTurmas();
    }

    private function cursosEEtapas(): void
    {
        $codEscola = $this->escola()->cod_escola;

        foreach (Catalogo::CURSOS as $siglaCurso => $definicao) {
            $this->firstOrCreate(TipoCurso::class, ['cod_escola' => $codEscola, 'nome' => $definicao['nivel']]);

            $curso = $this->firstOrCreate(Curso::class, ['cod_escola' => $codEscola, 'sigla' => $siglaCurso], [
                'nome' => $definicao['nome'],
            ]);

            foreach ($definicao['etapas'] as $codEtapa => [$nome, $sigla]) {
                $this->firstOrCreate(Etapa::class, ['cod_curso' => $curso->cod_curso, 'cod_etapa' => $codEtapa], [
                    'nome' => $nome,
                    'sigla' => $sigla,
                ]);
            }
        }
    }

    private function salas(): void
    {
        foreach (array_keys(Catalogo::UNIDADES) as $siglaUnidade) {
            $codUnidade = $this->unidade($siglaUnidade)->cod_unidade_escolar;

            $salas = [];
            foreach (range(1, Catalogo::SALAS_DE_AULA_POR_UNIDADE) as $numero) {
                $salas["Sala {$numero}"] = Catalogo::SALA_DE_AULA;
            }
            $salas += Catalogo::SALAS_ESPECIAIS[$siglaUnidade];

            foreach ($salas as $nome => $tipo) {
                $this->firstOrCreate(Sala::class, ['cod_unidade_escolar' => $codUnidade, 'nome' => $nome], [
                    'cod_tipo_sala' => $this->tipoSala($tipo)->cod_tipo_sala,
                    'descricao' => $tipo === Catalogo::SALA_DE_AULA ? null : "Espaço de uso compartilhado ({$tipo}).",
                ]);
            }
        }
    }

    /**
     * Um período por unidade e ano; o de 2026 aponta para o de 2025 como anterior.
     */
    private function periodosLetivos(): void
    {
        foreach (Catalogo::UNIDADES as $siglaUnidade => $unidade) {
            $codUnidade = $this->unidade($siglaUnidade)->cod_unidade_escolar;
            $codTipoCurso = TipoCurso::query()
                ->where('cod_escola', $this->escola()->cod_escola)
                ->where('nome', Catalogo::CURSOS[$unidade['curso']]['nivel'])
                ->firstOrFail()
                ->cod_tipo_curso;

            $anterior = null;

            foreach (Catalogo::PERIODOS as [$inicio, $fim]) {
                $criadoEm = CarbonImmutable::parse($inicio)->subMonths(3);

                $anterior = $this->firstOrCreate(PeriodoLetivo::class, [
                    'cod_unidade_escolar' => $codUnidade,
                    'cod_tipo_curso' => $codTipoCurso,
                    'data_inicio' => $inicio,
                ], [
                    'cod_periodo_letivo_anterior' => $anterior?->cod_periodo_letivo,
                    'data_fim' => $fim,
                    'created_at' => $criadoEm,
                    'updated_at' => $criadoEm,
                ]);
            }
        }
    }

    private function turmas(): void
    {
        foreach (Catalogo::TURMAS as $ano => $turmasPorCurso) {
            foreach ($turmasPorCurso as $siglaCurso => $turmas) {
                $siglaUnidade = Catalogo::unidadeDoCurso($siglaCurso);
                $periodo = $this->periodo($siglaUnidade, $ano);
                $curso = $this->curso($siglaCurso);
                $criadoEm = $periodo->data_inicio->copy()->subMonths(2);

                foreach ($turmas as [$codigo, $codEtapa, $siglaTurno, $nomeSala]) {
                    $nomeEtapa = Catalogo::CURSOS[$siglaCurso]['etapas'][$codEtapa][0];
                    $letra = substr($codigo, -1);

                    $this->firstOrCreate(Turma::class, [
                        'cod_periodo_letivo' => $periodo->cod_periodo_letivo,
                        'codigo_turma' => Catalogo::codigoTurma($siglaCurso, $codigo),
                    ], [
                        'cod_unidade_escolar' => $periodo->cod_unidade_escolar,
                        'cod_curso' => $curso->cod_curso,
                        'cod_etapa' => $codEtapa,
                        'cod_sala' => $this->sala($siglaUnidade, $nomeSala),
                        'cod_turno' => $this->turno($siglaTurno)->cod_turno,
                        'nome' => "{$nomeEtapa} – Turma {$letra}",
                        'nome_reduzido' => $codigo,
                        'created_at' => $criadoEm,
                        'updated_at' => $criadoEm,
                    ]);
                }
            }
        }
    }

    /**
     * Disciplinas das turmas de 2026 do Fundamental e do Médio (a Educação Infantil é polivalente).
     */
    private function disciplinasDasTurmas(): void
    {
        $ano = Catalogo::ANO_LETIVO_ATUAL;

        foreach (Catalogo::DISCIPLINAS_POR_CURSO as $siglaCurso => $disciplinas) {
            foreach (Catalogo::TURMAS[$ano][$siglaCurso] as [$codigo]) {
                $turma = $this->turma($ano, $siglaCurso, $codigo);

                foreach (array_keys($disciplinas) as $siglaDisciplina) {
                    $this->firstOrCreate(TurmaDisciplina::class, [
                        'cod_turma' => $turma->cod_turma,
                        'cod_disciplina' => $this->disciplina($siglaDisciplina)->cod_disciplina,
                    ]);
                }
            }
        }
    }

    private function sala(string $siglaUnidade, string $nome): int
    {
        return Sala::query()
            ->where('cod_unidade_escolar', $this->unidade($siglaUnidade)->cod_unidade_escolar)
            ->where('nome', $nome)
            ->firstOrFail()
            ->cod_sala;
    }
}
