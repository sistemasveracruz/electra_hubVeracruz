<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Enturma;
use App\Models\Familia;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\PessoaEndereco;
use App\Models\RegistroAcademico;
use App\Models\Responsavel;
use App\Models\ResponsavelFinanceiro;
use App\Models\ResponsavelPedagogico;
use App\Models\Turma;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Seeders\Support\Catalogo;
use Database\Seeders\Support\DevData;

/**
 * Famílias e alunos: pessoas, endereços, registro acadêmico, matrículas (2026 e,
 * para os promovidos, 2025), aluno, enturmações, vínculos familiares e responsáveis.
 */
class FamiliaAlunoSeeder extends DevSeeder
{
    /** Bases dos índices de CPF (ver DevData::cpf): base + família × 10 + posição. */
    private const CPF_ALUNOS = 1000;

    private const CPF_FAMILIARES = 3000;

    /**
     * Família => familiares (parentesco com os alunos) e alunos ("CURSO:TURMA:posição" na turma do ano atual).
     * Por padrão, o responsável financeiro é o primeiro familiar e o pedagógico é a mãe (ou o primeiro familiar).
     */
    private const FAMILIAS = [
        // Irmãos em unidades diferentes
        1 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EI:G3A:1', 'EFAI:3A:1']],
        2 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EI:G4A:1', 'EFAF:7A:1']],
        3 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EI:G5A:1', 'EFAI:5A:1'], 'pedagogicos' => [Catalogo::MAE, Catalogo::PAI]],
        4 => ['familiares' => [Catalogo::MAE, Catalogo::AVOA], 'alunos' => ['EI:G5B:1', 'EFAI:1A:1'], 'financeiro' => Catalogo::AVOA],
        5 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EFAI:5B:1', 'EFAF:9A:1']],
        6 => ['familiares' => [Catalogo::MAE], 'alunos' => ['EFAF:6A:1', 'EM:1A:1']],
        7 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EFAF:8A:1', 'EM:3A:1']],
        8 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EFAI:1A:2', 'EM:2A:2']],

        // Filho(a) único(a)
        9 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EI:G3A:2']],
        10 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EI:G4A:2']],
        11 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EI:G5A:2']],
        12 => ['familiares' => [Catalogo::PAI], 'alunos' => ['EI:G5B:2']],
        13 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EFAI:3A:2']],
        14 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EFAI:5A:2'], 'pedagogicos' => [Catalogo::MAE, Catalogo::PAI]],
        15 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EFAI:5B:2']],
        16 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EFAF:6A:2']],
        17 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EFAF:7A:2']],
        18 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EFAF:8A:2']],
        19 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EFAF:9A:2']],
        20 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EM:1A:2']],
        21 => ['familiares' => [Catalogo::PAI, Catalogo::MAE], 'alunos' => ['EM:2A:1']],
        22 => ['familiares' => [Catalogo::MAE, Catalogo::TIO], 'alunos' => ['EM:3A:2'], 'financeiro' => Catalogo::TIO],
    ];

    /** Alunos com situação diferente de "Matriculado" no ano atual => [status, data da mudança]. */
    private const SITUACOES = [
        'EFAI:3A:2' => [Catalogo::STATUS_CANCELADO, '2026-02-20 10:00:00'],
        'EM:2A:1' => [Catalogo::STATUS_TRANSFERIDO, '2026-05-20 10:00:00'],
        'EFAF:7A:2' => [Catalogo::STATUS_TRANCADO, '2026-06-15 10:00:00'],
    ];

    /** Endereços além do pessoal: [família, parentesco, tipo, excluído (soft delete)?]. */
    private const ENDERECOS_ADICIONAIS = [
        [1, Catalogo::PAI, 'CORRESPONDENCIA', false],
        [5, Catalogo::PAI, 'EMPRESARIAL', false],
        [7, Catalogo::MAE, 'CORRESPONDENCIA', true],
    ];

    private const PARENTESCOS_MASCULINOS = [Catalogo::PAI, Catalogo::AVO, Catalogo::TIO, Catalogo::IRMAO];

    protected function seed(): void
    {
        foreach (self::FAMILIAS as $numero => $familia) {
            $this->familia($numero, $familia);
        }
    }

    /**
     * @param  array{familiares: list<string>, alunos: list<string>, financeiro?: string, pedagogicos?: list<string>}  $familia
     */
    private function familia(int $numero, array $familia): void
    {
        $faker = DevData::faker();
        $sobrenome = $faker->lastName();
        $sobrenomeMaterno = $faker->lastName();
        $temPai = in_array(Catalogo::PAI, $familia['familiares'], true);
        $sobrenomeFilhos = $temPai ? "{$sobrenomeMaterno} {$sobrenome}" : $sobrenome;
        $endereco = DevData::endereco();

        $alunos = [];
        foreach ($familia['alunos'] as $posicao => $chave) {
            $alunos[] = $this->aluno(
                $chave,
                self::CPF_ALUNOS + $numero * 10 + $posicao,
                "{$faker->firstName()} {$sobrenomeFilhos}",
                $endereco,
            );
        }

        $idadeFilhoMaisVelho = max(array_column($alunos, 'idade'));
        $cadastradoEm = min(array_map(fn (array $aluno) => $aluno['pessoa']->created_at, $alunos));

        $familiares = [];
        foreach ($familia['familiares'] as $posicao => $parentesco) {
            $familiares[$parentesco] = $this->familiar(
                $parentesco,
                self::CPF_FAMILIARES + $numero * 10 + $posicao,
                $this->nomeFamiliar($parentesco, $sobrenome, $sobrenomeMaterno, $temPai),
                $this->idadeFamiliar($parentesco, $idadeFilhoMaisVelho),
                $endereco,
                $cadastradoEm,
            );
        }

        foreach (self::ENDERECOS_ADICIONAIS as [$numeroFamilia, $parentesco, $tipo, $excluido]) {
            if ($numeroFamilia === $numero) {
                $this->firstOrCreate(PessoaEndereco::class, [
                    'cod_pessoa' => $familiares[$parentesco]->cod_pessoa,
                    'tipo_endereco' => $tipo,
                ], DevData::endereco() + ($excluido
                    ? ['ativo' => false, 'deleted_at' => '2026-03-10 14:00:00']
                    : []));
            }
        }

        $this->vinculosFamiliares($familiares, $alunos);
        $this->responsaveis($familia, $familiares, $alunos);
    }

    /**
     * Cria (ou localiza) o aluno com matrícula e enturmação no ano atual e, se promovido, no ano anterior.
     *
     * @param  array<string, string|null>  $endereco
     * @return array{pessoa: Pessoa, aluno: Aluno, idade: int}
     */
    private function aluno(string $chave, int $indiceCpf, string $nome, array $endereco): array
    {
        [$siglaCurso, $codigo, $posicao] = explode(':', $chave);
        $ano = Catalogo::ANO_LETIVO_ATUAL;
        $turma = $this->turma($ano, $siglaCurso, $codigo);
        $idade = Catalogo::CURSOS[$siglaCurso]['etapas'][$turma->cod_etapa][2];
        $codigoAnterior = Catalogo::PROMOCOES[$siglaCurso][$codigo] ?? null;
        $anoIngresso = $codigoAnterior === null ? $ano : $ano - 1;
        $ingressoEm = $this->dataMatricula($anoIngresso, $indiceCpf);
        $faker = DevData::faker();

        $pessoa = $this->firstOrCreate(Pessoa::class, ['cpf' => DevData::cpf($indiceCpf)], [
            'nome' => $nome,
            'data_nascimento' => DevData::dataNascimento($idade),
            'email' => $idade >= 15 ? DevData::email($nome) : null,
            'telefone_fixo' => null,
            'telefone_pessoal' => $idade >= 15 ? $faker->cellphoneNumber() : null,
            'created_at' => $ingressoEm,
            'updated_at' => $ingressoEm,
        ]);

        $this->firstOrCreate(PessoaEndereco::class, [
            'cod_pessoa' => $pessoa->cod_pessoa,
            'tipo_endereco' => 'PESSOAL',
        ], $endereco + ['created_at' => $ingressoEm, 'updated_at' => $ingressoEm]);

        $registro = $this->firstOrCreate(RegistroAcademico::class, [
            'cod_pessoa' => $pessoa->cod_pessoa,
            'cod_escola' => $this->escola()->cod_escola,
        ], [
            'registro_academico' => $anoIngresso.sprintf('%05d', $indiceCpf - self::CPF_ALUNOS),
            'created_at' => $ingressoEm,
            'updated_at' => $ingressoEm,
        ]);

        $matriculaAnterior = null;
        if ($codigoAnterior !== null) {
            $matriculaAnterior = $this->matricula(
                $pessoa,
                $registro,
                $this->turma($ano - 1, $siglaCurso, $codigoAnterior),
                Catalogo::STATUS_CONCLUIDO,
                $ingressoEm,
                CarbonImmutable::parse(Catalogo::PERIODOS[$ano - 1][1])->setTime(18, 0),
            );
        }

        [$situacao, $mudancaEm] = self::SITUACOES[$chave] ?? [Catalogo::STATUS_MATRICULADO, null];
        $matriculadoEm = $this->dataMatricula($ano, $indiceCpf);

        $matricula = $this->matricula(
            $pessoa,
            $registro,
            $turma,
            $situacao,
            $matriculadoEm,
            $mudancaEm === null ? $matriculadoEm : CarbonImmutable::parse($mudancaEm),
        );

        $aluno = $this->firstOrCreate(Aluno::class, ['cod_pessoa' => $pessoa->cod_pessoa], [
            'cod_registro_academico' => $registro->cod_registro_academico,
            'cod_matricula' => $matricula->cod_matricula,
            'ativo' => $matricula->ativo,
            'created_at' => $ingressoEm,
            'updated_at' => $matricula->updated_at,
        ]);

        if ($matriculaAnterior !== null) {
            $this->enturma($aluno, $matriculaAnterior, $posicao);
        }
        $this->enturma($aluno, $matricula, $posicao);

        return ['pessoa' => $pessoa, 'aluno' => $aluno, 'idade' => $idade];
    }

    private function matricula(
        Pessoa $pessoa,
        RegistroAcademico $registro,
        Turma $turma,
        string $situacao,
        CarbonInterface $criadaEm,
        CarbonInterface $atualizadaEm,
    ): Matricula {
        return $this->firstOrCreate(Matricula::class, [
            'cod_pessoa' => $pessoa->cod_pessoa,
            'cod_turma' => $turma->cod_turma,
        ], [
            'cod_escola' => $registro->cod_escola,
            'cod_unidade_escolar' => $turma->cod_unidade_escolar,
            'cod_curso' => $turma->cod_curso,
            'cod_etapa' => $turma->cod_etapa,
            'cod_status' => $this->status($situacao)->cod_status,
            'cod_registro_academico' => $registro->cod_registro_academico,
            // Transferência e cancelamento encerram a matrícula; trancamento a mantém.
            'ativo' => ! in_array($situacao, [Catalogo::STATUS_TRANSFERIDO, Catalogo::STATUS_CANCELADO], true),
            'created_at' => $criadaEm,
            'updated_at' => $atualizadaEm,
        ]);
    }

    private function enturma(Aluno $aluno, Matricula $matricula, string $posicao): void
    {
        $this->firstOrCreate(Enturma::class, [
            'cod_matricula' => $matricula->cod_matricula,
            'cod_turma' => $matricula->cod_turma,
        ], [
            'cod_aluno' => $aluno->cod_aluno,
            'cod_status' => $matricula->cod_status,
            'numero_chamada' => sprintf('%02d', (int) $posicao),
            'ativo' => $matricula->ativo,
            'created_at' => $matricula->created_at,
            'updated_at' => $matricula->updated_at,
        ]);
    }

    /**
     * @param  array<string, string|null>  $endereco
     */
    private function familiar(
        string $parentesco,
        int $indiceCpf,
        string $nome,
        int $idade,
        array $endereco,
        CarbonInterface $cadastradoEm,
    ): Pessoa {
        $faker = DevData::faker();

        $pessoa = $this->firstOrCreate(Pessoa::class, ['cpf' => DevData::cpf($indiceCpf)], [
            'nome' => $nome,
            'data_nascimento' => DevData::dataNascimento($idade),
            'email' => DevData::email($nome),
            'telefone_fixo' => $faker->optional(0.4)->landlineNumber(),
            'telefone_pessoal' => $faker->cellphoneNumber(),
            'telefone_empresa' => $faker->optional(0.3)->landlineNumber(),
            'created_at' => $cadastradoEm,
            'updated_at' => $cadastradoEm,
        ]);

        $this->firstOrCreate(PessoaEndereco::class, [
            'cod_pessoa' => $pessoa->cod_pessoa,
            'tipo_endereco' => 'PESSOAL',
        ], $endereco + ['created_at' => $cadastradoEm, 'updated_at' => $cadastradoEm]);

        return $pessoa;
    }

    /**
     * Uma linha por familiar × aluno, todas com o mesmo cod_familia.
     *
     * @param  array<string, Pessoa>  $familiares
     * @param  list<array{pessoa: Pessoa, aluno: Aluno, idade: int}>  $alunos
     */
    private function vinculosFamiliares(array $familiares, array $alunos): void
    {
        $codFamilia = null;

        foreach ($familiares as $familiar) {
            foreach ($alunos as $aluno) {
                $vinculo = $this->firstOrCreate(Familia::class, [
                    'cod_pessoa' => $familiar->cod_pessoa,
                    'cod_pessoa_aluno' => $aluno['pessoa']->cod_pessoa,
                ], $codFamilia === null ? [] : ['cod_familia' => $codFamilia]);

                $codFamilia ??= $vinculo->cod_familia;
            }
        }
    }

    /**
     * @param  array{familiares: list<string>, alunos: list<string>, financeiro?: string, pedagogicos?: list<string>}  $familia
     * @param  array<string, Pessoa>  $familiares
     * @param  list<array{pessoa: Pessoa, aluno: Aluno, idade: int}>  $alunos
     */
    private function responsaveis(array $familia, array $familiares, array $alunos): void
    {
        $financeiro = $familia['financeiro'] ?? $familia['familiares'][0];
        $pedagogicos = $familia['pedagogicos']
            ?? [in_array(Catalogo::MAE, $familia['familiares'], true) ? Catalogo::MAE : $familia['familiares'][0]];

        foreach ($alunos as $aluno) {
            $codPessoaAluno = $aluno['pessoa']->cod_pessoa;

            $responsavelFinanceiro = $this->firstOrCreate(ResponsavelFinanceiro::class, [
                'cod_pessoa_aluno' => $codPessoaAluno,
                'cod_pessoa_responsavel' => $familiares[$financeiro]->cod_pessoa,
            ], [
                'cod_parentesco_responsavel' => $this->parentesco($financeiro)->cod_parentesco,
            ]);

            foreach ($pedagogicos as $pedagogico) {
                $responsavelPedagogico = $this->firstOrCreate(ResponsavelPedagogico::class, [
                    'cod_pessoa_aluno' => $codPessoaAluno,
                    'cod_pessoa_responsavel' => $familiares[$pedagogico]->cod_pessoa,
                ], [
                    'cod_parentesco_responsavel' => $this->parentesco($pedagogico)->cod_parentesco,
                ]);

                $this->firstOrCreate(Responsavel::class, [
                    'cod_aluno' => $aluno['aluno']->cod_aluno,
                    'cod_responsavel_pedagogico' => $responsavelPedagogico->cod_responsavel_pedagogico,
                    'cod_responsavel_financeiro' => $responsavelFinanceiro->cod_responsavel_financeiro,
                ], [
                    'cod_pessoa_aluno' => $codPessoaAluno,
                ]);
            }
        }
    }

    private function nomeFamiliar(string $parentesco, string $sobrenome, string $sobrenomeMaterno, bool $temPai): string
    {
        $faker = DevData::faker();
        $primeiroNome = in_array($parentesco, self::PARENTESCOS_MASCULINOS, true)
            ? $faker->firstNameMale()
            : $faker->firstNameFemale();

        return match ($parentesco) {
            Catalogo::PAI => "{$primeiroNome} {$sobrenome}",
            Catalogo::MAE => $temPai ? "{$primeiroNome} {$sobrenomeMaterno} {$sobrenome}" : "{$primeiroNome} {$sobrenome}",
            // Demais familiares são do lado materno.
            default => "{$primeiroNome} {$sobrenomeMaterno}",
        };
    }

    private function idadeFamiliar(string $parentesco, int $idadeFilhoMaisVelho): int
    {
        $faker = DevData::faker();

        return match ($parentesco) {
            Catalogo::PAI => $idadeFilhoMaisVelho + $faker->numberBetween(27, 40),
            Catalogo::MAE => $idadeFilhoMaisVelho + $faker->numberBetween(25, 38),
            Catalogo::AVO, Catalogo::AVOA => $faker->numberBetween(60, 72),
            default => $faker->numberBetween(30, 48),
        };
    }

    /**
     * Data de matrícula para o ano letivo: entre o início de novembro do ano anterior e meados de janeiro.
     */
    private function dataMatricula(int $anoLetivo, int $indice): CarbonImmutable
    {
        return CarbonImmutable::create($anoLetivo - 1, 11, 3, 9)->addDays($indice % 70);
    }
}
