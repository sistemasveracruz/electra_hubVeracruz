<?php

namespace Database\Seeders;

use App\Models\Funcionario;
use App\Models\Pessoa;
use App\Models\PessoaEndereco;
use App\Models\TurmaDisciplina;
use App\Models\TurmaDisciplinaFuncionario;
use App\Models\TurmaFuncionario;
use App\Models\VinculoFuncionario;
use Carbon\CarbonImmutable;
use Database\Seeders\Support\Catalogo;
use Database\Seeders\Support\DevData;

/**
 * Equipe do Instituto Vera Cruz: pessoas, funcionários, vínculos por unidade,
 * atuação nas turmas (titular, auxiliar, coordenação) e atribuição de disciplinas.
 */
class FuncionarioSeeder extends DevSeeder
{
    /** Base do índice de CPF dos funcionários (ver DevData::cpf). */
    private const CPF_BASE = 2000;

    /**
     * Chave => dados do funcionário. "n" é o índice estável do CPF; "unidades" gera um vínculo por unidade.
     */
    private const EQUIPE = [
        // Educação Infantil (Verinha)
        'ei_g3a' => ['n' => 1, 'cargo' => 'Professor(a) de Educação Infantil', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERINHA']],
        'ei_g4a' => ['n' => 2, 'cargo' => 'Professor(a) de Educação Infantil', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERINHA']],
        'ei_g5a' => ['n' => 3, 'cargo' => 'Professor(a) de Educação Infantil', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERINHA']],
        'ei_g5b' => ['n' => 4, 'cargo' => 'Professor(a) de Educação Infantil', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERINHA']],
        'aux_ei_1' => ['n' => 5, 'cargo' => 'Professor(a) Auxiliar', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERINHA']],
        'aux_ei_2' => ['n' => 6, 'cargo' => 'Professor(a) Auxiliar', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERINHA']],

        // Fundamental – Anos Iniciais (Verão): polivalentes
        'efai_1a' => ['n' => 7, 'cargo' => 'Professor(a) Polivalente', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO']],
        'efai_3a' => ['n' => 8, 'cargo' => 'Professor(a) Polivalente', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO']],
        'efai_5a' => ['n' => 9, 'cargo' => 'Professor(a) Polivalente', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO']],
        'efai_5b' => ['n' => 10, 'cargo' => 'Professor(a) Polivalente', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO']],

        // Especialistas (alguns com vínculo em mais de uma unidade)
        'lp_ef' => ['n' => 11, 'cargo' => 'Professor(a) de Língua Portuguesa', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO2']],
        'lp_em' => ['n' => 12, 'cargo' => 'Professor(a) de Língua Portuguesa', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['BAUMMAN']],
        'mat' => ['n' => 13, 'cargo' => 'Professor(a) de Matemática', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO2', 'BAUMMAN']],
        'cie_bio' => ['n' => 14, 'cargo' => 'Professor(a) de Ciências e Biologia', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO2', 'BAUMMAN']],
        'his' => ['n' => 15, 'cargo' => 'Professor(a) de História', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO2', 'BAUMMAN']],
        'geo' => ['n' => 16, 'cargo' => 'Professor(a) de Geografia', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO2', 'BAUMMAN']],
        'fis' => ['n' => 17, 'cargo' => 'Professor(a) de Física', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['BAUMMAN']],
        'qui' => ['n' => 18, 'cargo' => 'Professor(a) de Química', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['BAUMMAN']],
        'fil_soc' => ['n' => 19, 'cargo' => 'Professor(a) de Filosofia e Sociologia', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['BAUMMAN']],
        'ing' => ['n' => 20, 'cargo' => 'Professor(a) de Língua Inglesa', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO', 'VERAO2', 'BAUMMAN']],
        'edf' => ['n' => 21, 'cargo' => 'Professor(a) de Educação Física', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO', 'VERAO2', 'BAUMMAN']],
        'art' => ['n' => 22, 'cargo' => 'Professor(a) de Arte', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERAO', 'VERAO2', 'BAUMMAN']],
        'lp_sub' => ['n' => 23, 'cargo' => 'Professor(a) de Língua Portuguesa', 'setor' => 'Pedagógico', 'vinculo' => 'Prazo determinado', 'professor' => true, 'unidades' => ['VERAO2'], 'admissao' => '2026-03-25 00:00:00'],

        // Coordenação (uma por unidade)
        'coord_verinha' => ['n' => 24, 'cargo' => 'Coordenador(a) Pedagógico(a)', 'setor' => 'Coordenação Pedagógica', 'vinculo' => 'CLT', 'professor' => false, 'unidades' => ['VERINHA']],
        'coord_verao' => ['n' => 25, 'cargo' => 'Coordenador(a) Pedagógico(a)', 'setor' => 'Coordenação Pedagógica', 'vinculo' => 'CLT', 'professor' => false, 'unidades' => ['VERAO']],
        'coord_verao2' => ['n' => 26, 'cargo' => 'Coordenador(a) Pedagógico(a)', 'setor' => 'Coordenação Pedagógica', 'vinculo' => 'CLT', 'professor' => false, 'unidades' => ['VERAO2']],
        'coord_baumman' => ['n' => 27, 'cargo' => 'Coordenador(a) Pedagógico(a)', 'setor' => 'Coordenação Pedagógica', 'vinculo' => 'CLT', 'professor' => false, 'unidades' => ['BAUMMAN']],

        // Secretaria
        'sec_1' => ['n' => 28, 'cargo' => 'Secretário(a) Escolar', 'setor' => 'Secretaria', 'vinculo' => 'CLT', 'professor' => false, 'unidades' => ['VERINHA', 'VERAO']],
        'sec_2' => ['n' => 29, 'cargo' => 'Secretário(a) Escolar', 'setor' => 'Secretaria', 'vinculo' => 'CLT', 'professor' => false, 'unidades' => ['VERAO2', 'BAUMMAN']],

        // Desligado ao fim de 2025 (atuou como auxiliar no G4A de 2025)
        'aux_desligado' => ['n' => 30, 'cargo' => 'Professor(a) Auxiliar', 'setor' => 'Pedagógico', 'vinculo' => 'CLT', 'professor' => true, 'unidades' => ['VERINHA'], 'admissao' => '2023-02-01 00:00:00', 'desligamento' => '2025-12-19 00:00:00'],
    ];

    /** Atuação nas turmas: [ano, curso, turma, funcionário, função, fim (opcional)]. */
    private const ATUACOES = [
        [2026, 'EI', 'G3A', 'ei_g3a', Catalogo::FUNCAO_TITULAR],
        [2026, 'EI', 'G3A', 'aux_ei_1', Catalogo::FUNCAO_AUXILIAR],
        [2026, 'EI', 'G4A', 'ei_g4a', Catalogo::FUNCAO_TITULAR],
        [2026, 'EI', 'G4A', 'aux_ei_2', Catalogo::FUNCAO_AUXILIAR],
        [2026, 'EI', 'G5A', 'ei_g5a', Catalogo::FUNCAO_TITULAR],
        [2026, 'EI', 'G5B', 'ei_g5b', Catalogo::FUNCAO_TITULAR],
        [2026, 'EFAI', '1A', 'efai_1a', Catalogo::FUNCAO_TITULAR],
        [2026, 'EFAI', '3A', 'efai_3a', Catalogo::FUNCAO_TITULAR],
        [2026, 'EFAI', '5A', 'efai_5a', Catalogo::FUNCAO_TITULAR],
        [2026, 'EFAI', '5B', 'efai_5b', Catalogo::FUNCAO_TITULAR],
        [2025, 'EI', 'G4A', 'aux_desligado', Catalogo::FUNCAO_AUXILIAR, '2025-12-12 00:00:00'],
    ];

    /** Coordenador(a) de cada unidade, que atua em todas as turmas do ano atual. */
    private const COORDENACAO = [
        'VERINHA' => 'coord_verinha',
        'VERAO' => 'coord_verao',
        'VERAO2' => 'coord_verao2',
        'BAUMMAN' => 'coord_baumman',
    ];

    /**
     * Professor por disciplina e curso. No Fundamental – Anos Iniciais, as disciplinas
     * não listadas ficam com o(a) titular polivalente da turma.
     */
    private const PROFESSORES_POR_DISCIPLINA = [
        'EFAI' => ['ING' => 'ing', 'EDF' => 'edf', 'ART' => 'art'],
        'EFAF' => [
            'LP' => 'lp_ef', 'MAT' => 'mat', 'CIE' => 'cie_bio', 'HIS' => 'his',
            'GEO' => 'geo', 'ART' => 'art', 'EDF' => 'edf', 'ING' => 'ing',
        ],
        'EM' => [
            'LP' => 'lp_em', 'MAT' => 'mat', 'FIS' => 'fis', 'QUI' => 'qui', 'BIO' => 'cie_bio', 'HIS' => 'his',
            'GEO' => 'geo', 'FIL' => 'fil_soc', 'SOC' => 'fil_soc', 'ING' => 'ing', 'EDF' => 'edf', 'ART' => 'art',
        ],
    ];

    /** Substituições temporárias: [curso, turma, disciplina, substituto, início, fim]. */
    private const SUBSTITUICOES = [
        ['EFAF', '7A', 'LP', 'lp_sub', '2026-04-01 00:00:00', '2026-06-30 00:00:00'],
    ];

    /** @var array<string, Funcionario> */
    private array $funcionarios = [];

    protected function seed(): void
    {
        foreach (self::EQUIPE as $chave => $dados) {
            $this->funcionarios[$chave] = $this->funcionario($dados);
        }

        $this->atuacoesNasTurmas();
        $this->atribuicoesDeDisciplinas();
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function funcionario(array $dados): Funcionario
    {
        $faker = DevData::faker();
        $primeiroNome = $faker->boolean() ? $faker->firstNameFemale() : $faker->firstNameMale();
        $nome = "{$primeiroNome} {$faker->lastName()} {$faker->lastName()}";
        $admissao = CarbonImmutable::parse($dados['admissao'] ?? $faker->dateTimeBetween('2012-01-01', '2024-12-31'));
        $desligamento = $dados['desligamento'] ?? null;

        $pessoa = $this->firstOrCreate(Pessoa::class, ['cpf' => DevData::cpf(self::CPF_BASE + $dados['n'])], [
            'nome' => $nome,
            'data_nascimento' => DevData::dataNascimento($faker->numberBetween(24, 60)),
            'email' => DevData::email($nome, 'veracruz.example'),
            'email_pessoal' => DevData::email($nome),
            'telefone_fixo' => null,
            'telefone_pessoal' => $faker->cellphoneNumber(),
            'telefone_empresa' => '(11) 3555-0100',
            'created_at' => $admissao,
            'updated_at' => $admissao,
        ]);

        $this->firstOrCreate(PessoaEndereco::class, [
            'cod_pessoa' => $pessoa->cod_pessoa,
            'tipo_endereco' => 'PESSOAL',
        ], DevData::endereco() + ['created_at' => $admissao, 'updated_at' => $admissao]);

        $funcionario = $this->firstOrCreate(Funcionario::class, ['cod_pessoa' => $pessoa->cod_pessoa], [
            'professor_cadastro' => $dados['professor'],
            'ativo' => $desligamento === null,
            'created_at' => $admissao,
            'updated_at' => $desligamento ?? $admissao,
        ]);

        foreach ($dados['unidades'] as $siglaUnidade) {
            $unidade = $this->unidade($siglaUnidade);

            $this->firstOrCreate(VinculoFuncionario::class, [
                'cod_funcionario' => $funcionario->cod_funcionario,
                'cod_unidade_escolar' => $unidade->cod_unidade_escolar,
            ], [
                'cod_escola' => $unidade->cod_escola,
                'cargo' => $dados['cargo'],
                'setor' => $dados['setor'],
                'tipo_vinculo' => $dados['vinculo'],
                'data_admissao' => $admissao,
                'data_desligamento' => $desligamento,
                'ativo' => $desligamento === null,
                'created_at' => $admissao,
                'updated_at' => $desligamento ?? $admissao,
            ]);
        }

        return $funcionario;
    }

    private function atuacoesNasTurmas(): void
    {
        foreach (self::ATUACOES as $atuacao) {
            [$ano, $siglaCurso, $codigo, $chave, $funcao] = $atuacao;

            $this->atuacao($ano, $siglaCurso, $codigo, $chave, $funcao, $atuacao[5] ?? null);
        }

        $ano = Catalogo::ANO_LETIVO_ATUAL;

        foreach (self::COORDENACAO as $siglaUnidade => $chave) {
            $siglaCurso = Catalogo::UNIDADES[$siglaUnidade]['curso'];

            foreach (Catalogo::TURMAS[$ano][$siglaCurso] as [$codigo]) {
                $this->atuacao($ano, $siglaCurso, $codigo, $chave, Catalogo::FUNCAO_COORDENACAO);
            }
        }
    }

    private function atuacao(int $ano, string $siglaCurso, string $codigo, string $chave, string $funcao, ?string $fim = null): void
    {
        $turma = $this->turma($ano, $siglaCurso, $codigo);
        $inicio = $turma->periodoLetivo->data_inicio;

        $this->firstOrCreate(TurmaFuncionario::class, [
            'cod_turma' => $turma->cod_turma,
            'cod_funcionario' => $this->funcionarios[$chave]->cod_funcionario,
            'cod_funcao_turma' => $this->funcaoTurma($funcao)->cod_funcao_turma,
        ], [
            'data_inicio' => $inicio,
            'data_fim' => $fim,
            'ativo' => $fim === null,
            'created_at' => $turma->created_at,
            'updated_at' => $fim ?? $turma->created_at,
        ]);
    }

    private function atribuicoesDeDisciplinas(): void
    {
        $ano = Catalogo::ANO_LETIVO_ATUAL;

        foreach (Catalogo::DISCIPLINAS_POR_CURSO as $siglaCurso => $disciplinas) {
            foreach (Catalogo::TURMAS[$ano][$siglaCurso] as [$codigo]) {
                foreach (array_keys($disciplinas) as $siglaDisciplina) {
                    $chave = self::PROFESSORES_POR_DISCIPLINA[$siglaCurso][$siglaDisciplina]
                        ?? $this->titular($siglaCurso, $codigo);

                    $this->atribuicao($ano, $siglaCurso, $codigo, $siglaDisciplina, $chave, 'TITULAR');
                }
            }
        }

        foreach (self::SUBSTITUICOES as [$siglaCurso, $codigo, $siglaDisciplina, $chave, $inicio, $fim]) {
            $this->atribuicao($ano, $siglaCurso, $codigo, $siglaDisciplina, $chave, 'SUBSTITUTO', $inicio, $fim);
        }
    }

    private function atribuicao(
        int $ano,
        string $siglaCurso,
        string $codigo,
        string $siglaDisciplina,
        string $chave,
        string $tipoAtribuicao,
        ?string $inicio = null,
        ?string $fim = null,
    ): void {
        $turma = $this->turma($ano, $siglaCurso, $codigo);

        $turmaDisciplina = TurmaDisciplina::query()
            ->where('cod_turma', $turma->cod_turma)
            ->where('cod_disciplina', $this->disciplina($siglaDisciplina)->cod_disciplina)
            ->firstOrFail();

        $this->firstOrCreate(TurmaDisciplinaFuncionario::class, [
            'cod_turma_disciplina' => $turmaDisciplina->cod_turma_disciplina,
            'cod_funcionario' => $this->funcionarios[$chave]->cod_funcionario,
        ], [
            'tipo_atribuicao' => $tipoAtribuicao,
            'data_inicio' => $inicio ?? $turma->periodoLetivo->data_inicio,
            'data_fim' => $fim,
            'created_at' => $turma->created_at,
            'updated_at' => $turma->created_at,
        ]);
    }

    /**
     * Chave do(a) titular da turma no ano atual (Educação Infantil e Fundamental – Anos Iniciais).
     */
    private function titular(string $siglaCurso, string $codigo): string
    {
        foreach (self::ATUACOES as [$ano, $curso, $turma, $chave, $funcao]) {
            if ($ano === Catalogo::ANO_LETIVO_ATUAL && $curso === $siglaCurso && $turma === $codigo && $funcao === Catalogo::FUNCAO_TITULAR) {
                return $chave;
            }
        }

        throw new \LogicException("Turma {$siglaCurso}-{$codigo} sem titular definido.");
    }
}
