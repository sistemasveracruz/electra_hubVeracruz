<?php

namespace App\Console\Commands;

use App\Models\Escola;
use App\Models\Pessoa;
use App\Models\RegistroAcademico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Traz os alunos do TOTVS RM para o HUB:
 *   PPESSOA -> inst_pessoas             (casamento por CPF)
 *   SALUNO  -> edu_registros_academicos (casamento por escola + RA)
 *
 * edu_alunos não é preenchida aqui: exige cod_matricula, que depende de curso/etapa/turma.
 */
class SincronizarAlunosRm extends Command
{
    protected $signature = 'rm:sincronizar-alunos
        {--coligada=1 : CODCOLIGADA no RM}
        {--escola= : cod_escola no HUB (padrão: o mesmo número da coligada)}
        {--periodo= : CODPERLET; sincroniza só os alunos matriculados nesse período letivo}
        {--dry-run : Executa tudo e desfaz no final, só para ver o resultado}';

    protected $description = 'Sincroniza pessoas e registros acadêmicos dos alunos a partir do TOTVS RM';

    private array $totais = [
        'pessoas_criadas' => 0,
        'pessoas_atualizadas' => 0,
        'ras_criados' => 0,
        'ras_atualizados' => 0,
        'ignorados' => 0,
    ];

    public function handle(): int
    {
        $coligada = (int) $this->option('coligada');
        $codEscola = (int) ($this->option('escola') ?: $coligada);
        $periodo = $this->option('periodo');

        if (! Escola::find($codEscola)) {
            $this->error("Escola {$codEscola} não existe no HUB. Informe --escola.");

            return self::FAILURE;
        }

        $query = DB::connection('rm')
            ->table('SALUNO as A')
            ->join('PPESSOA as P', 'P.CODIGO', '=', 'A.CODPESSOA')
            ->where('A.CODCOLIGADA', $coligada)
            ->select('A.RA', 'P.CODIGO', 'P.NOME', 'P.CPF', 'P.DTNASCIMENTO', 'P.EMAIL', 'P.TELEFONE1', 'P.TELEFONE2', 'P.TELEFONE3')
            ->orderBy('A.RA');

        if ($periodo) {
            $query->whereExists(function ($sub) use ($periodo) {
                $sub->selectRaw('1')
                    ->from('SMATRICPL as M')
                    ->join('SPLETIVO as PL', function ($join) {
                        $join->on('PL.CODCOLIGADA', '=', 'M.CODCOLIGADA')
                            ->on('PL.IDPERLET', '=', 'M.IDPERLET');
                    })
                    ->whereColumn('M.CODCOLIGADA', 'A.CODCOLIGADA')
                    ->whereColumn('M.RA', 'A.RA')
                    ->where('PL.CODPERLET', $periodo);
            });
        }

        // Uma transação para a execução inteira: se algo falhar no meio, nada fica pela metade,
        // e o --dry-run aproveita a mesma transação para desfazer tudo no final.
        DB::beginTransaction();

        try {
            foreach ($query->lazy(500) as $linha) {
                $this->sincronizar($linha, $codEscola);
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
        }

        $this->table(['Resultado', 'Quantidade'], collect($this->totais)->map(fn ($n, $k) => [$k, $n])->values());

        return self::SUCCESS;
    }

    private function sincronizar(object $linha, int $codEscola): void
    {
        $ra = trim($linha->RA);
        $cpf = preg_replace('/\D/', '', (string) $linha->CPF);

        if (strlen($cpf) !== 11) {
            return $this->ignorar($ra, 'sem CPF válido no RM');
        }

        if (strlen($ra) > 12) {
            return $this->ignorar($ra, 'RA com mais de 12 caracteres');
        }

        $pessoa = Pessoa::withTrashed()->where('cpf', $cpf)->first();

        if ($pessoa?->trashed()) {
            return $this->ignorar($ra, "pessoa {$pessoa->cod_pessoa} excluída no HUB");
        }

        if (! $pessoa) {
            // Pessoa nova recebe o mesmo código da PPESSOA, desde que ele esteja livre no HUB.
            if (Pessoa::withTrashed()->whereKey($linha->CODIGO)->exists()) {
                return $this->ignorar($ra, "cod_pessoa {$linha->CODIGO} já usado por outro CPF");
            }

            $pessoa = new Pessoa(['cod_pessoa' => $linha->CODIGO, 'cpf' => $cpf]);
        }

        // Só sobrescreve com o que vier preenchido no RM, para não apagar dados digitados no HUB.
        $pessoa->fill(array_filter([
            'nome' => trim((string) $linha->NOME),
            'data_nascimento' => $linha->DTNASCIMENTO,
            'email' => trim((string) $linha->EMAIL),
            'telefone_fixo' => trim((string) $linha->TELEFONE1),
            'telefone_pessoal' => trim((string) $linha->TELEFONE2),
            'telefone_empresa' => trim((string) $linha->TELEFONE3),
        ], fn ($valor) => $valor !== null && $valor !== ''));

        $this->contar($pessoa, 'pessoas');
        $pessoa->save();

        $registro = RegistroAcademico::withTrashed()->firstOrNew([
            'cod_escola' => $codEscola,
            'registro_academico' => $ra,
        ]);

        if ($registro->exists && $registro->cod_pessoa !== $pessoa->cod_pessoa) {
            return $this->ignorar($ra, "RA já pertence à pessoa {$registro->cod_pessoa} no HUB");
        }

        $registro->cod_pessoa = $pessoa->cod_pessoa;

        $this->contar($registro, 'ras');
        $registro->save();
    }

    private function contar(object $model, string $tipo): void
    {
        if (! $model->exists) {
            $this->totais["{$tipo}_criadas"] = ($this->totais["{$tipo}_criadas"] ?? 0) + 0;
            $this->totais[$tipo === 'pessoas' ? 'pessoas_criadas' : 'ras_criados']++;
        } elseif ($model->isDirty()) {
            $this->totais[$tipo === 'pessoas' ? 'pessoas_atualizadas' : 'ras_atualizados']++;
        }
    }

    private function ignorar(string $ra, string $motivo): void
    {
        $this->totais['ignorados']++;
        $this->line("<comment>RA {$ra} ignorado:</comment> {$motivo}");
    }
}
