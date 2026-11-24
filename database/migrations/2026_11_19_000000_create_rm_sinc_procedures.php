<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cria (ou atualiza) as procedures de sincronização com o TOTVS RM a partir dos arquivos
 * em database/scripts/procedures. Cada arquivo tem um único CREATE OR ALTER PROCEDURE, sem GO.
 *
 * Ao alterar uma procedure, edite o .sql e rode o arquivo no SSMS (ou php artisan migrate:fresh).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->arquivos() as $arquivo) {
            DB::unprepared(file_get_contents($arquivo));
        }
    }

    public function down(): void
    {
        foreach ($this->arquivos() as $arquivo) {
            if (preg_match('/CREATE\s+OR\s+ALTER\s+PROCEDURE\s+dbo\.(\w+)/i', file_get_contents($arquivo), $nome)) {
                DB::unprepared("DROP PROCEDURE IF EXISTS dbo.{$nome[1]}");
            }
        }
    }

    /** @return list<string> */
    private function arquivos(): array
    {
        $arquivos = glob(database_path('scripts/procedures/*.sql')) ?: [];
        sort($arquivos, SORT_NATURAL);

        return $arquivos;
    }
};
