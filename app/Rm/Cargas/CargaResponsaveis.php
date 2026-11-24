<?php

namespace App\Rm\Cargas;

use App\Models\Aluno;
use App\Models\Responsavel;
use App\Models\ResponsavelFinanceiro;
use App\Models\ResponsavelPedagogico;
use App\Rm\Carga;
use Illuminate\Database\Eloquent\Model;

/**
 * Responsáveis do aluno, a partir de SALUNO:
 *   CODPESSOARACA          -> edu_responsaveis_pedagogico (pessoa já trazida pela carga pessoas)
 *   CODCOLCFO + CODCFO     -> edu_responsaveis_financeiros (pessoa criada a partir de FCFO)
 *   os dois juntos         -> edu_responsaveis
 * Quando o RM troca o responsável, o anterior fica inativo no HUB.
 *
 * O parentesco (CODPARENTRACA/CODPARENTCFO) ainda não é mapeado para aux_parentesco.
 */
class CargaResponsaveis extends Carga
{
    protected bool $incremental = true;

    public static function nome(): string
    {
        return 'responsaveis';
    }

    public function executar(): void
    {
        $query = $this->rm()->table('dbo.SALUNO as A')
            ->leftJoin('dbo.FCFO as F', function ($join) {
                $join->on('F.CODCOLIGADA', '=', 'A.CODCOLCFO')->on('F.CODCFO', '=', 'A.CODCFO');
            })
            ->where('A.CODCOLIGADA', $this->coligada)
            ->select('A.RA', 'A.CODPESSOARACA', 'A.CODCOLCFO', 'A.CODCFO',
                'F.NOME', 'F.CGCCFO', 'F.PESSOAFISOUJUR', 'F.DTNASCIMENTO', 'F.EMAIL', 'F.TELEFONE')
            ->orderBy('A.RA');

        foreach ($this->alteradoDesde($query, 'A', 'F')->lazy(500) as $linha) {
            $ra = trim($linha->RA);
            $codAluno = $this->codHub("{$this->coligada}|{$ra}", 'alunos');
            $aluno = $codAluno === null ? null : Aluno::find($codAluno);

            // Sem aluno no HUB (sem matrícula), não há a quem ligar o responsável.
            if (! $aluno) {
                continue;
            }

            $pedagogico = $linha->CODPESSOARACA
                ? $this->pedagogico($aluno, $ra, (string) $linha->CODPESSOARACA)
                : null;

            $financeiro = $linha->CODCFO && $linha->NOME
                ? $this->financeiro($aluno, $ra, $linha)
                : null;

            if ($pedagogico && $financeiro) {
                $this->vincular($aluno, $pedagogico, $financeiro);
            }
        }
    }

    private function pedagogico(Aluno $aluno, string $ra, string $codPessoaRm): ?ResponsavelPedagogico
    {
        $codPessoa = $this->codHub($codPessoaRm, 'pessoas');

        if ($codPessoa === null) {
            $this->ignorar("RA {$ra}", "responsável acadêmico {$codPessoaRm} não carregado");

            return null;
        }

        return $this->responsavel(ResponsavelPedagogico::class, $aluno->cod_pessoa, $codPessoa);
    }

    private function financeiro(Aluno $aluno, string $ra, object $linha): ?ResponsavelFinanceiro
    {
        // CGCCFO guarda CPF ou CNPJ; só pessoa física tem CPF para casar com inst_pessoas.
        $cpf = $linha->PESSOAFISOUJUR === 'F' ? $linha->CGCCFO : null;

        $pessoa = $this->sincronizarPessoa("CFO|{$linha->CODCOLCFO}|{$linha->CODCFO}", null, $cpf, [
            'nome' => $this->texto($linha->NOME, 150),
            'data_nascimento' => $linha->DTNASCIMENTO,
            'email' => $this->texto($linha->EMAIL, 250),
            'telefone_pessoal' => $this->texto($linha->TELEFONE, 20),
        ]);

        return $pessoa ? $this->responsavel(ResponsavelFinanceiro::class, $aluno->cod_pessoa, $pessoa->cod_pessoa) : null;
    }

    /**
     * @template TModel of ResponsavelPedagogico|ResponsavelFinanceiro
     *
     * @param  class-string<TModel>  $model
     * @return TModel|null
     */
    private function responsavel(string $model, int $codPessoaAluno, int $codPessoaResponsavel): ?Model
    {
        $responsavel = $model::withTrashed()->firstOrNew([
            'cod_pessoa_aluno' => $codPessoaAluno,
            'cod_pessoa_responsavel' => $codPessoaResponsavel,
        ]);

        if ($responsavel->trashed()) {
            return null;
        }

        $responsavel->ativo = true;
        $this->salvar($responsavel);

        $model::where('cod_pessoa_aluno', $codPessoaAluno)
            ->where('cod_pessoa_responsavel', '!=', $codPessoaResponsavel)
            ->where('ativo', true)
            ->update(['ativo' => false]);

        return $responsavel;
    }

    private function vincular(Aluno $aluno, ResponsavelPedagogico $pedagogico, ResponsavelFinanceiro $financeiro): void
    {
        $chave = [
            'cod_aluno' => $aluno->cod_aluno,
            'cod_responsavel_pedagogico' => $pedagogico->cod_responsavel_pedagogico,
            'cod_responsavel_financeiro' => $financeiro->cod_responsavel_financeiro,
        ];

        // edu_responsaveis não tem "ativo": o vínculo anterior do aluno é substituído.
        Responsavel::where('cod_aluno', $aluno->cod_aluno)
            ->where(fn ($q) => $q->where('cod_responsavel_pedagogico', '!=', $chave['cod_responsavel_pedagogico'])
                ->orWhere('cod_responsavel_financeiro', '!=', $chave['cod_responsavel_financeiro']))
            ->delete();

        if (! Responsavel::where($chave)->exists()) {
            Responsavel::create($chave + ['cod_pessoa_aluno' => $aluno->cod_pessoa]);
        }
    }
}
