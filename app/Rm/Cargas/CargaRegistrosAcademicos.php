<?php

namespace App\Rm\Cargas;

use App\Models\RegistroAcademico;
use App\Rm\Carga;

/** SALUNO -> edu_registros_academicos (RA do aluno na escola). */
class CargaRegistrosAcademicos extends Carga
{
    protected bool $incremental = true;

    public static function nome(): string
    {
        return 'ras';
    }

    public function executar(): void
    {
        $codEscola = $this->codEscola();

        $query = $this->rm()->table('dbo.SALUNO as A')
            ->where('A.CODCOLIGADA', $this->coligada)
            ->select('A.RA', 'A.CODPESSOA')
            ->orderBy('A.RA');

        foreach ($this->alteradoDesde($query, 'A')->lazy(500) as $linha) {
            $ra = trim($linha->RA);
            $chave = "{$this->coligada}|{$ra}";
            $codPessoa = $this->codHub((string) $linha->CODPESSOA, 'pessoas');

            if ($codPessoa === null) {
                $this->ignorar("RA {$ra}", "pessoa {$linha->CODPESSOA} não carregada");

                continue;
            }

            if (strlen($ra) > 12) {
                $this->ignorar("RA {$ra}", 'RA com mais de 12 caracteres');

                continue;
            }

            $registro = $this->localizar(RegistroAcademico::class, $chave, fn () => RegistroAcademico::withTrashed()->firstOrNew([
                'cod_escola' => $codEscola,
                'registro_academico' => $ra,
            ]));

            if (! $registro) {
                continue;
            }

            $this->preencher($registro, ['cod_pessoa' => $codPessoa]);
            $this->gravar($registro, $chave);
        }
    }
}
