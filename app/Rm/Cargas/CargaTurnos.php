<?php

namespace App\Rm\Cargas;

use App\Models\Turno;
use App\Rm\Carga;

/** STURNO -> edu_turnos. */
class CargaTurnos extends Carga
{
    public static function nome(): string
    {
        return 'turnos';
    }

    public function executar(): void
    {
        $linhas = $this->rm()->table('dbo.STURNO')
            ->where('CODCOLIGADA', $this->coligada)
            ->get(['CODTURNO', 'NOME']);

        foreach ($linhas as $linha) {
            $chave = "{$this->coligada}|{$linha->CODTURNO}";

            $turno = $this->localizar(Turno::class, $chave, fn () => new Turno([
                'cod_turno' => $this->novoCodigo(Turno::class, 'cod_turno', $linha->CODTURNO),
            ]));

            $this->preencher($turno, [
                'nome' => $this->texto($linha->NOME, 80) ?? "Turno {$linha->CODTURNO}",
            ]);

            $this->gravar($turno, $chave);
        }
    }
}
