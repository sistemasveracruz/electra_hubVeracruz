<?php

namespace App\Rm\Cargas;

use App\Models\PessoaEndereco;
use App\Rm\Carga;

/**
 * PPESSOA -> inst_pessoas + endereço PESSOAL em inst_pessoa_enderecos.
 * Traz os alunos da coligada (SALUNO.CODPESSOA) e os responsáveis acadêmicos (SALUNO.CODPESSOARACA).
 * O responsável financeiro vem de FCFO, na carga responsaveis.
 */
class CargaPessoas extends Carga
{
    protected bool $incremental = true;

    public static function nome(): string
    {
        return 'pessoas';
    }

    public function executar(): void
    {
        // A pessoa entra se ela ou o vínculo com o aluno mudou: um aluno pode passar a apontar
        // para um responsável que já existia no RM sem ter sido alterado.
        $query = $this->rm()->table('dbo.PPESSOA as P')
            ->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('dbo.SALUNO as S')
                    ->where('S.CODCOLIGADA', $this->coligada)
                    ->where(fn ($q) => $q->whereColumn('S.CODPESSOA', 'P.CODIGO')->orWhereColumn('S.CODPESSOARACA', 'P.CODIGO'));

                $this->alteradoDesde($sub, 'P', 'S');
            })
            ->select('P.CODIGO', 'P.NOME', 'P.CPF', 'P.DTNASCIMENTO', 'P.EMAIL', 'P.EMAILPESSOAL', 'P.TELEFONE1', 'P.TELEFONE2', 'P.TELEFONE3',
                'P.RUA', 'P.NUMERO', 'P.COMPLEMENTO', 'P.BAIRRO', 'P.CIDADE', 'P.ESTADO', 'P.CEP')
            ->orderBy('P.CODIGO');

        foreach ($query->lazy(500) as $linha) {
            $pessoa = $this->sincronizarPessoa((string) $linha->CODIGO, $linha->CODIGO, $linha->CPF, [
                'nome' => $this->texto($linha->NOME, 150),
                'data_nascimento' => $linha->DTNASCIMENTO,
                'email' => $this->texto($linha->EMAIL, 250),
                'email_pessoal' => $this->texto($linha->EMAILPESSOAL, 250),
                'telefone_fixo' => $this->texto($linha->TELEFONE1, 20),
                'telefone_pessoal' => $this->texto($linha->TELEFONE2, 20),
                'telefone_empresa' => $this->texto($linha->TELEFONE3, 20),
            ]);

            if ($pessoa) {
                $this->sincronizarEndereco($pessoa->cod_pessoa, $linha);
            }
        }
    }

    private function sincronizarEndereco(int $codPessoa, object $linha): void
    {
        $valores = [
            'cep' => $this->texto($this->digitos($linha->CEP), 8),
            'logradouro' => $this->texto($linha->RUA, 200),
            'numero' => $this->texto($linha->NUMERO, 20),
            'complemento' => $this->texto($linha->COMPLEMENTO, 100),
            'bairro' => $this->texto($linha->BAIRRO, 100),
            'cidade' => $this->texto($linha->CIDADE, 100),
            'uf' => $this->texto($linha->ESTADO, 2),
        ];

        if (! array_filter($valores)) {
            return;
        }

        $endereco = PessoaEndereco::withTrashed()->firstOrNew(['cod_pessoa' => $codPessoa, 'tipo_endereco' => 'PESSOAL']);

        if ($endereco->trashed()) {
            return;
        }

        $this->preencher($endereco, $valores);

        if ($endereco->isDirty()) {
            $endereco->save();
        }
    }
}
