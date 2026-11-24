<?php

namespace App\Rm\Cargas;

use App\Models\TipoCurso;
use App\Rm\Carga;

/** STIPOCURSO -> edu_tipo_cursos (nível de ensino). */
class CargaTiposCurso extends Carga
{
    public static function nome(): string
    {
        return 'tipos-curso';
    }

    public function executar(): void
    {
        $codEscola = $this->codEscola();

        $linhas = $this->rm()->table('dbo.STIPOCURSO')
            ->where('CODCOLIGADA', $this->coligada)
            ->get(['CODTIPOCURSO', 'NOME']);

        foreach ($linhas as $linha) {
            $chave = "{$this->coligada}|{$linha->CODTIPOCURSO}";

            $tipo = $this->localizar(TipoCurso::class, $chave, fn () => new TipoCurso([
                'cod_tipo_curso' => $this->novoCodigo(TipoCurso::class, 'cod_tipo_curso', $linha->CODTIPOCURSO),
            ]));

            if (! $tipo) {
                continue;
            }

            $this->preencher($tipo, [
                'cod_escola' => $codEscola,
                'nome' => $this->texto($linha->NOME, 150) ?? "Tipo de curso {$linha->CODTIPOCURSO}",
            ]);

            $this->gravar($tipo, $chave);
        }
    }
}
