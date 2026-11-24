<?php

namespace App\Rm\Cargas;

use App\Models\UnidadeEscolar;
use App\Rm\Carga;

/** GFILIAL -> inst_unidades_escolares (cada unidade do HUB é uma filial no RM). */
class CargaUnidades extends Carga
{
    public static function nome(): string
    {
        return 'unidades';
    }

    public function executar(): void
    {
        $codEscola = $this->codEscola();

        $linhas = $this->rm()->table('dbo.GFILIAL')
            ->where('CODCOLIGADA', $this->coligada)
            ->get(['CODFILIAL', 'NOME', 'NOMEFANTASIA', 'ATIVO']);

        foreach ($linhas as $linha) {
            $chave = "{$this->coligada}|{$linha->CODFILIAL}";

            $unidade = $this->localizar(UnidadeEscolar::class, $chave, fn () => new UnidadeEscolar([
                'cod_unidade_escolar' => $this->novoCodigo(UnidadeEscolar::class, 'cod_unidade_escolar', $linha->CODFILIAL),
            ]));

            if (! $unidade) {
                continue;
            }

            $this->preencher($unidade, [
                'cod_escola' => $codEscola,
                'nome' => $this->texto($linha->NOME, 150),
                'sigla' => $this->texto($linha->NOMEFANTASIA, 20),
                'ativo' => $linha->ATIVO === null ? null : (bool) $linha->ATIVO,
            ]);

            $this->gravar($unidade, $chave);
        }
    }
}
