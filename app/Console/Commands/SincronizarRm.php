<?php

namespace App\Console\Commands;

use App\Rm\Carga;
use App\Rm\Cargas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Carrega no HUB, a partir do TOTVS RM (somente leitura), tudo o que compõe a consulta do aluno:
 * escola, unidades, estrutura acadêmica, turmas, pessoas, RAs, matrículas, alunos, enturmações
 * e responsáveis. Ver App\Rm\Carga para as regras comuns.
 */
class SincronizarRm extends Command
{
    /** Ordem de dependência: cada carga só usa o que as anteriores já gravaram. */
    private const CARGAS = [
        Cargas\CargaEscola::class,
        Cargas\CargaUnidades::class,
        Cargas\CargaTiposCurso::class,
        Cargas\CargaTurnos::class,
        Cargas\CargaStatus::class,
        Cargas\CargaCursos::class,
        Cargas\CargaEtapas::class,
        Cargas\CargaPeriodos::class,
        Cargas\CargaTurmas::class,
        Cargas\CargaPessoas::class,
        Cargas\CargaRegistrosAcademicos::class,
        Cargas\CargaMatriculas::class,
        Cargas\CargaAlunos::class,
        Cargas\CargaEnturmas::class,
        Cargas\CargaResponsaveis::class,
    ];

    protected $signature = 'rm:sincronizar
        {--coligada=1 : CODCOLIGADA no RM}
        {--so=* : Roda só estas cargas (ex.: --so=turmas --so=matriculas)}
        {--completa : Ignora as marcas incrementais e relê tudo}
        {--dry-run : Executa tudo e desfaz no final, só para ver o resultado}';

    protected $description = 'Sincroniza o HUB com o TOTVS RM (escola, estrutura acadêmica e alunos)';

    public function handle(): int
    {
        $so = $this->option('so');

        /** @var \Illuminate\Support\Collection<int, Carga> $cargas */
        $cargas = collect(self::CARGAS)
            ->filter(fn (string $classe) => ! $so || in_array($classe::nome(), $so, true))
            ->map(fn (string $classe) => new $classe($this, (int) $this->option('coligada'), (bool) $this->option('completa')))
            ->values();

        if ($cargas->isEmpty()) {
            $this->error('Nenhuma carga com esse nome. Disponíveis: '.collect(self::CARGAS)->map(fn ($c) => $c::nome())->join(', '));

            return self::FAILURE;
        }

        // A marca da próxima execução vem do relógio do servidor do RM, antes da leitura:
        // o que for alterado durante a execução entra de novo na próxima, em vez de se perder.
        $inicio = DB::connection('rm')->selectOne('SELECT CONVERT(varchar(23), GETDATE(), 121) AS agora')->agora;

        // Uma transação para a execução inteira: se algo falhar no meio, nada fica pela metade,
        // e o --dry-run aproveita a mesma transação para desfazer tudo no final.
        DB::beginTransaction();

        try {
            foreach ($cargas as $carga) {
                $this->info('» '.$carga::nome());
                $carga->executar();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        if ($this->option('dry-run')) {
            DB::rollBack();
            $this->warn('Dry-run: nenhuma alteração foi gravada.');
        } else {
            DB::commit();

            $cargas->filter->incremental()->each(fn (Carga $carga) => Cache::forever($carga->chaveMarca(), $inicio));
        }

        $this->table(
            ['Carga', 'Criados', 'Atualizados', 'Ignorados'],
            $cargas->map(fn (Carga $carga) => [$carga::nome(), ...array_values($carga->totais())]),
        );

        return self::SUCCESS;
    }
}
