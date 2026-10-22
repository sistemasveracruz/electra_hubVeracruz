<?php

namespace Database\Seeders\Support;

use InvalidArgumentException;

/**
 * Dados de referência do ambiente de desenvolvimento.
 *
 * Os seeders e os states das factories localizam registros por estas chaves
 * naturais (nomes, siglas, códigos), nunca por IDs fixos.
 */
final class Catalogo
{
    public const ANO_LETIVO_ATUAL = 2026;

    public const ANO_LETIVO_ANTERIOR = 2025;

    /** Ano => [data_inicio, data_fim] dos períodos letivos. */
    public const PERIODOS = [
        self::ANO_LETIVO_ANTERIOR => ['2025-02-03 00:00:00', '2025-12-12 00:00:00'],
        self::ANO_LETIVO_ATUAL => ['2026-02-02 00:00:00', '2026-12-11 00:00:00'],
    ];

    public const SEMANAS_LETIVAS = 40;

    // ----- Instituição ----- //

    public const ESCOLA_NOME = 'Instituto Vera Cruz';

    public const ESCOLA_NOME_FANTASIA = 'Vera Cruz';

    private const ESCOLA_CNPJ_INDICE = 1;

    /** Sigla => dados da unidade e o curso (nível de ensino) que ela oferece. */
    public const UNIDADES = [
        'VERINHA' => ['nome' => 'Verinha', 'descricao' => 'Unidade de Educação Infantil', 'curso' => 'EI'],
        'VERAO' => ['nome' => 'Verão', 'descricao' => 'Unidade do Ensino Fundamental – Anos Iniciais', 'curso' => 'EFAI'],
        'VERAO2' => ['nome' => 'Verão²', 'descricao' => 'Unidade do Ensino Fundamental – Anos Finais', 'curso' => 'EFAF'],
        'BAUMMAN' => ['nome' => 'Baumman', 'descricao' => 'Unidade do Ensino Médio', 'curso' => 'EM'],
    ];

    /**
     * Sigla do curso => nome, nível (edu_tipo_cursos) e etapas (cod_etapa => [nome, sigla, idade de referência]).
     */
    public const CURSOS = [
        'EI' => [
            'nome' => 'Educação Infantil',
            'nivel' => 'Educação Infantil',
            'etapas' => [
                1 => ['Grupo 3', 'G3', 3],
                2 => ['Grupo 4', 'G4', 4],
                3 => ['Grupo 5', 'G5', 5],
            ],
        ],
        'EFAI' => [
            'nome' => 'Ensino Fundamental – Anos Iniciais',
            'nivel' => 'Ensino Fundamental – Anos Iniciais',
            'etapas' => [
                1 => ['1º ano', '1º', 6],
                2 => ['2º ano', '2º', 7],
                3 => ['3º ano', '3º', 8],
                4 => ['4º ano', '4º', 9],
                5 => ['5º ano', '5º', 10],
            ],
        ],
        'EFAF' => [
            'nome' => 'Ensino Fundamental – Anos Finais',
            'nivel' => 'Ensino Fundamental – Anos Finais',
            'etapas' => [
                6 => ['6º ano', '6º', 11],
                7 => ['7º ano', '7º', 12],
                8 => ['8º ano', '8º', 13],
                9 => ['9º ano', '9º', 14],
            ],
        ],
        'EM' => [
            'nome' => 'Ensino Médio',
            'nivel' => 'Ensino Médio',
            'etapas' => [
                1 => ['1ª série', '1ª', 15],
                2 => ['2ª série', '2ª', 16],
                3 => ['3ª série', '3ª', 17],
            ],
        ],
    ];

    // ----- Turnos ----- //

    public const TURNO_MANHA = 'M';

    public const TURNO_TARDE = 'T';

    public const TURNO_INTEGRAL = 'I';

    /** Sigla => nome. */
    public const TURNOS = [
        self::TURNO_MANHA => 'Manhã',
        self::TURNO_TARDE => 'Tarde',
        self::TURNO_INTEGRAL => 'Integral',
    ];

    // ----- Status de matrícula/enturmação ----- //

    public const STATUS_MATRICULADO = 'Matriculado';

    public const STATUS_TRANCADO = 'Trancado';

    public const STATUS_TRANSFERIDO = 'Transferido';

    public const STATUS_CANCELADO = 'Cancelado';

    public const STATUS_CONCLUIDO = 'Concluído';

    /** Nome => descrição. */
    public const STATUS = [
        self::STATUS_MATRICULADO => 'Matrícula vigente no período letivo.',
        self::STATUS_TRANCADO => 'Matrícula suspensa temporariamente a pedido da família.',
        self::STATUS_TRANSFERIDO => 'Aluno transferido para outra instituição.',
        self::STATUS_CANCELADO => 'Matrícula cancelada antes da conclusão do período.',
        self::STATUS_CONCLUIDO => 'Período letivo concluído.',
    ];

    // ----- Tipos de sala ----- //

    public const SALA_DE_AULA = 'Sala de aula';

    public const LAB_CIENCIAS = 'Laboratório de Ciências';

    public const LAB_INFORMATICA = 'Laboratório de Informática';

    public const BIBLIOTECA = 'Biblioteca';

    public const QUADRA = 'Quadra poliesportiva';

    /** Nome => permite_reserva. */
    public const TIPOS_SALA = [
        self::SALA_DE_AULA => false,
        self::LAB_CIENCIAS => true,
        self::LAB_INFORMATICA => true,
        self::BIBLIOTECA => true,
        self::QUADRA => true,
    ];

    public const SALAS_DE_AULA_POR_UNIDADE = 4;

    /** Sigla da unidade => [nome da sala => tipo de sala]. */
    public const SALAS_ESPECIAIS = [
        'VERINHA' => ['Biblioteca Infantil' => self::BIBLIOTECA],
        'VERAO' => ['Laboratório de Informática' => self::LAB_INFORMATICA, 'Quadra' => self::QUADRA],
        'VERAO2' => ['Laboratório de Ciências' => self::LAB_CIENCIAS, 'Laboratório de Informática' => self::LAB_INFORMATICA],
        'BAUMMAN' => ['Laboratório de Ciências' => self::LAB_CIENCIAS, 'Biblioteca' => self::BIBLIOTECA, 'Quadra' => self::QUADRA],
    ];

    // ----- Disciplinas ----- //

    /** Sigla => nome. */
    public const DISCIPLINAS = [
        'LP' => 'Língua Portuguesa',
        'MAT' => 'Matemática',
        'CIE' => 'Ciências',
        'HIS' => 'História',
        'GEO' => 'Geografia',
        'ART' => 'Arte',
        'EDF' => 'Educação Física',
        'ING' => 'Língua Inglesa',
        'FIS' => 'Física',
        'QUI' => 'Química',
        'BIO' => 'Biologia',
        'FIL' => 'Filosofia',
        'SOC' => 'Sociologia',
    ];

    /** Disciplinas descontinuadas (ativo = false), mantidas para histórico. */
    public const DISCIPLINAS_INATIVAS = [
        'ESP' => 'Língua Espanhola',
    ];

    /** Sigla do curso => [sigla da disciplina => aulas semanais]. A Educação Infantil é polivalente. */
    public const DISCIPLINAS_POR_CURSO = [
        'EFAI' => ['LP' => 5, 'MAT' => 5, 'CIE' => 2, 'HIS' => 2, 'GEO' => 2, 'ART' => 2, 'EDF' => 2, 'ING' => 2],
        'EFAF' => ['LP' => 5, 'MAT' => 5, 'CIE' => 3, 'HIS' => 2, 'GEO' => 2, 'ART' => 2, 'EDF' => 2, 'ING' => 2],
        'EM' => [
            'LP' => 4, 'MAT' => 4, 'FIS' => 2, 'QUI' => 2, 'BIO' => 2, 'HIS' => 2,
            'GEO' => 2, 'FIL' => 1, 'SOC' => 1, 'ING' => 2, 'EDF' => 2, 'ART' => 1,
        ],
    ];

    /** Sigla do curso => disciplinas não obrigatórias na grade. */
    public const DISCIPLINAS_OPTATIVAS = [
        'EM' => ['ART'],
    ];

    // ----- Parentescos ----- //

    public const PAI = 'Pai';

    public const MAE = 'Mãe';

    public const AVO = 'Avô';

    public const AVOA = 'Avó';

    public const TIO = 'Tio';

    public const TIA = 'Tia';

    public const IRMAO = 'Irmão';

    public const IRMA = 'Irmã';

    public const RESPONSAVEL_LEGAL = 'Responsável legal';

    public const PARENTESCOS = [
        self::PAI, self::MAE, self::AVO, self::AVOA, self::TIO,
        self::TIA, self::IRMAO, self::IRMA, self::RESPONSAVEL_LEGAL,
    ];

    // ----- Funções de turma ----- //

    public const FUNCAO_TITULAR = 'PROF_TITULAR';

    public const FUNCAO_AUXILIAR = 'PROF_AUXILIAR';

    public const FUNCAO_COORDENACAO = 'COORDENACAO';

    /** Código => descrição. */
    public const FUNCOES_TURMA = [
        self::FUNCAO_TITULAR => 'Professor(a) titular da turma',
        self::FUNCAO_AUXILIAR => 'Professor(a) auxiliar da turma',
        self::FUNCAO_COORDENACAO => 'Coordenação pedagógica da turma',
    ];

    // ----- Prefixos (domínios do banco) ----- //

    /** Sigla => descrição. */
    public const PREFIXOS = [
        'INST' => 'Institucional',
        'EDU' => 'Educacional',
        'FUN' => 'Funcionários',
        'AUX' => 'Auxiliares',
        'DOC' => 'Documentação',
    ];

    // ----- Turmas ----- //

    /**
     * Ano => sigla do curso => lista de [código, cod_etapa, sigla do turno, nome da sala].
     * O código é único dentro do curso e período; o codigo_turma gravado é "{CURSO}-{código}".
     */
    public const TURMAS = [
        self::ANO_LETIVO_ATUAL => [
            'EI' => [
                ['G3A', 1, self::TURNO_MANHA, 'Sala 1'],
                ['G4A', 2, self::TURNO_INTEGRAL, 'Sala 2'],
                ['G5A', 3, self::TURNO_MANHA, 'Sala 3'],
                ['G5B', 3, self::TURNO_TARDE, 'Sala 3'],
            ],
            'EFAI' => [
                ['1A', 1, self::TURNO_MANHA, 'Sala 1'],
                ['3A', 3, self::TURNO_MANHA, 'Sala 2'],
                ['5A', 5, self::TURNO_MANHA, 'Sala 3'],
                ['5B', 5, self::TURNO_TARDE, 'Sala 3'],
            ],
            'EFAF' => [
                ['6A', 6, self::TURNO_TARDE, 'Sala 1'],
                ['7A', 7, self::TURNO_TARDE, 'Sala 2'],
                ['8A', 8, self::TURNO_TARDE, 'Sala 3'],
                ['9A', 9, self::TURNO_TARDE, 'Sala 4'],
            ],
            'EM' => [
                ['1A', 1, self::TURNO_MANHA, 'Sala 1'],
                ['2A', 2, self::TURNO_MANHA, 'Sala 2'],
                ['3A', 3, self::TURNO_MANHA, 'Sala 3'],
            ],
        ],
        self::ANO_LETIVO_ANTERIOR => [
            'EI' => [['G4A', 2, self::TURNO_INTEGRAL, 'Sala 2']],
            'EFAI' => [['4A', 4, self::TURNO_MANHA, 'Sala 1']],
            'EFAF' => [['8A', 8, self::TURNO_TARDE, 'Sala 3']],
            'EM' => [['2A', 2, self::TURNO_MANHA, 'Sala 2']],
        ],
    ];

    /** Sigla do curso => [turma do ano atual => turma do ano anterior de onde os alunos foram promovidos]. */
    public const PROMOCOES = [
        'EI' => ['G5A' => 'G4A'],
        'EFAI' => ['5A' => '4A'],
        'EFAF' => ['9A' => '8A'],
        'EM' => ['3A' => '2A'],
    ];

    public static function escolaCnpj(): string
    {
        return DevData::cnpj(self::ESCOLA_CNPJ_INDICE);
    }

    public static function unidadeDoCurso(string $siglaCurso): string
    {
        foreach (self::UNIDADES as $sigla => $unidade) {
            if ($unidade['curso'] === $siglaCurso) {
                return $sigla;
            }
        }

        throw new InvalidArgumentException("Nenhuma unidade oferece o curso {$siglaCurso}.");
    }

    public static function codigoTurma(string $siglaCurso, string $codigo): string
    {
        return "{$siglaCurso}-{$codigo}";
    }
}
