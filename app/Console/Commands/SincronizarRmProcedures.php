<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Executa dbo.rm_sinc_tudo, que sincroniza o HUB com o TOTVS RM (INSERT, UPDATE e soft delete).
 * As procedures ficam em database/scripts/procedures e são criadas pela migration
 * 2026_11_19_000000_create_rm_sinc_procedures. Agendado em routes/console.php.
 */
class SincronizarRmProcedures extends Command
{
    protected $signature = 'rm:sincronizar-procedures
        {--dry-run : Executa tudo e desfaz no final, só para ver se roda sem erro}';

    protected $description = 'Sincroniza o HUB com o TOTVS RM pelas procedures dbo.rm_sinc_*';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $inicio = microtime(true);

        if ($dryRun) {
            DB::beginTransaction();
        }

        try {
            DB::unprepared('EXEC dbo.rm_sinc_tudo');
        } catch (\Throwable $e) {
            $this->desfazer($dryRun);
            Log::error('rm:sincronizar-procedures falhou', ['erro' => $e->getMessage()]);

            throw $e;
        }

        $this->desfazer($dryRun);

        $segundos = round(microtime(true) - $inicio, 1);

        if ($dryRun) {
            $this->warn("Dry-run: executou em {$segundos}s e nenhuma alteração foi gravada.");
        } else {
            Log::info('rm:sincronizar-procedures concluído', ['segundos' => $segundos]);
            $this->info("Sincronização concluída em {$segundos}s.");
        }

        return self::SUCCESS;
    }

    /** No dry-run desfaz a transação aberta antes do EXEC (a procedure pode já ter desfeito tudo após um erro). */
    private function desfazer(bool $dryRun): void
    {
        if (! $dryRun) {
            return;
        }

        try {
            DB::rollBack();
        } catch (\Throwable) {
            // Sem transação ativa: o ROLLBACK da procedure já desfez tudo.
        }
    }
}
