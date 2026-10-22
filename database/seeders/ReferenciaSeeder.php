<?php

namespace Database\Seeders;

use App\Models\Disciplina;
use App\Models\FuncaoTurma;
use App\Models\Parentesco;
use App\Models\Prefixo;
use App\Models\Status;
use App\Models\TipoSala;
use App\Models\Turno;
use Database\Seeders\Support\Catalogo;

/**
 * Tabelas de referência: turnos, status, disciplinas, tipos de sala,
 * parentescos, funções de turma e prefixos de domínio.
 */
class ReferenciaSeeder extends DevSeeder
{
    protected function seed(): void
    {
        foreach (Catalogo::TURNOS as $sigla => $nome) {
            $this->firstOrCreate(Turno::class, ['sigla' => $sigla], ['nome' => $nome]);
        }

        foreach (Catalogo::STATUS as $nome => $descricao) {
            $this->firstOrCreate(Status::class, ['nome' => $nome], ['descricao' => $descricao]);
        }

        foreach (Catalogo::DISCIPLINAS as $sigla => $nome) {
            $this->firstOrCreate(Disciplina::class, ['sigla' => $sigla], ['nome' => $nome]);
        }

        foreach (Catalogo::DISCIPLINAS_INATIVAS as $sigla => $nome) {
            $this->firstOrCreate(Disciplina::class, ['sigla' => $sigla], [
                'nome' => $nome,
                'observacoes' => 'Disciplina descontinuada; mantida para consulta de históricos.',
                'ativo' => false,
            ]);
        }

        foreach (Catalogo::TIPOS_SALA as $nome => $permiteReserva) {
            $this->firstOrCreate(TipoSala::class, ['nome' => $nome], ['permite_reserva' => $permiteReserva]);
        }

        foreach (Catalogo::PARENTESCOS as $descricao) {
            $this->firstOrCreate(Parentesco::class, ['descricao' => $descricao]);
        }

        foreach (Catalogo::FUNCOES_TURMA as $codigo => $descricao) {
            $this->firstOrCreate(FuncaoTurma::class, ['codigo' => $codigo], ['descricao' => $descricao]);
        }

        foreach (Catalogo::PREFIXOS as $sigla => $descricao) {
            $this->firstOrCreate(Prefixo::class, ['sigla' => $sigla], ['descricao' => $descricao]);
        }
    }
}
