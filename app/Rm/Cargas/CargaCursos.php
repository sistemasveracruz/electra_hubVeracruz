<?php

namespace App\Rm\Cargas;

use App\Models\Curso;
use App\Rm\Carga;

/** SCURSO -> edu_cursos. CODCURSO é varchar no RM; a sigla do HUB guarda o código original. */
class CargaCursos extends Carga
{
    public static function nome(): string
    {
        return 'cursos';
    }

    public function executar(): void
    {
        $codEscola = $this->codEscola();

        $linhas = $this->rm()->table('dbo.SCURSO')
            ->where('CODCOLIGADA', $this->coligada)
            ->get(['CODCURSO', 'NOME']);

        foreach ($linhas as $linha) {
            $chave = "{$this->coligada}|{$linha->CODCURSO}";

            $curso = $this->localizar(Curso::class, $chave, fn () => new Curso([
                'cod_curso' => $this->novoCodigo(Curso::class, 'cod_curso', $linha->CODCURSO),
            ]));

            if (! $curso) {
                continue;
            }

            $this->preencher($curso, [
                'cod_escola' => $codEscola,
                'nome' => $this->texto($linha->NOME, 150) ?? $linha->CODCURSO,
                'sigla' => $this->texto($linha->CODCURSO, 12),
            ]);

            $this->gravar($curso, $chave);
        }
    }
}
