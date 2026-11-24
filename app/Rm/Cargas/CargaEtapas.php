<?php

namespace App\Rm\Cargas;

use App\Models\Etapa;
use App\Rm\Carga;

/** SHABILITACAO -> edu_etapas: a habilitação do RM é a série/ano do curso. */
class CargaEtapas extends Carga
{
    public static function nome(): string
    {
        return 'etapas';
    }

    public function executar(): void
    {
        $linhas = $this->rm()->table('dbo.SHABILITACAO')
            ->where('CODCOLIGADA', $this->coligada)
            ->get(['CODCURSO', 'CODHABILITACAO', 'NOME']);

        foreach ($linhas as $linha) {
            $chave = "{$this->coligada}|{$linha->CODCURSO}|{$linha->CODHABILITACAO}";
            $codCurso = $this->codHub("{$this->coligada}|{$linha->CODCURSO}", 'cursos');

            if ($codCurso === null) {
                $this->ignorar($chave, "curso {$linha->CODCURSO} não carregado");

                continue;
            }

            // PK composto (cod_curso, cod_etapa): o código da etapa é gerado dentro do curso.
            $etapa = $this->localizar(Etapa::class, $chave, fn () => new Etapa([
                'cod_curso' => $codCurso,
                'cod_etapa' => $this->novoCodigo(Etapa::class, 'cod_etapa', $linha->CODHABILITACAO, ['cod_curso' => $codCurso]),
            ]), ['cod_curso' => $codCurso]);

            if (! $etapa) {
                continue;
            }

            $this->preencher($etapa, [
                'nome' => $this->texto($linha->NOME, 150) ?? $linha->CODHABILITACAO,
                'sigla' => $this->texto($linha->CODHABILITACAO, 12),
            ]);

            $this->gravar($etapa, $chave);
        }
    }
}
