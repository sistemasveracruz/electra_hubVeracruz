<?php

namespace App\Rm\Cargas;

use App\Models\Escola;
use App\Rm\Carga;

/** GCOLIGADA -> inst_escolas (só a coligada informada em --coligada). */
class CargaEscola extends Carga
{
    public static function nome(): string
    {
        return 'escola';
    }

    public function executar(): void
    {
        $linhas = $this->rm()->table('dbo.GCOLIGADA')
            ->where('CODCOLIGADA', $this->coligada)
            ->get(['CODCOLIGADA', 'NOME', 'NOMEFANTASIA', 'CGC']);

        foreach ($linhas as $linha) {
            $chave = (string) $linha->CODCOLIGADA;
            $cnpj = $this->digitos($linha->CGC);

            $escola = $this->localizar(Escola::class, $chave, fn () => new Escola([
                'cod_escola' => $this->novoCodigo(Escola::class, 'cod_escola', $linha->CODCOLIGADA),
            ]));

            if (! $escola) {
                continue;
            }

            // cnpj é obrigatório no HUB.
            if (strlen($cnpj) !== 14 && ! $escola->exists) {
                $this->ignorar("coligada {$chave}", 'sem CNPJ válido (GCOLIGADA.CGC)');

                continue;
            }

            $this->preencher($escola, [
                'nome' => $this->texto($linha->NOME, 150),
                'nome_fantasia' => $this->texto($linha->NOMEFANTASIA, 150),
                'cnpj' => strlen($cnpj) === 14 ? $cnpj : null,
            ]);

            $this->gravar($escola, $chave);
        }
    }
}
