<?php

namespace App\Rm\Cargas;

use App\Models\Status;
use App\Rm\Carga;

/** SSTATUS -> edu_status (situação da matrícula). */
class CargaStatus extends Carga
{
    public static function nome(): string
    {
        return 'status';
    }

    public function executar(): void
    {
        $linhas = $this->rm()->table('dbo.SSTATUS')
            ->where('CODCOLIGADA', $this->coligada)
            ->get(['CODSTATUS', 'DESCRICAO']);

        foreach ($linhas as $linha) {
            $chave = "{$this->coligada}|{$linha->CODSTATUS}";

            $status = $this->localizar(Status::class, $chave, fn () => new Status([
                'cod_status' => $this->novoCodigo(Status::class, 'cod_status', $linha->CODSTATUS),
            ]));

            if (! $status) {
                continue;
            }

            $this->preencher($status, [
                'nome' => $this->texto($linha->DESCRICAO, 120) ?? "Status {$linha->CODSTATUS}",
            ]);

            $this->gravar($status, $chave);
        }
    }
}
