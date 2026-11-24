<?php

namespace App\Rm;

use App\Models\Pessoa;
use App\Models\RmChave;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Base das cargas do TOTVS RM para o HUB, orquestradas pelo rm:sincronizar.
 *
 * Cada carga lê uma origem do RM (somente leitura, sempre do schema dbo: o RM mantém cópias
 * de auditoria das tabelas no schema TOTVSAUDIT) e grava de forma idempotente no HUB:
 * localiza o registro pelo de-para (aux_rm_chaves), cria se não existir e só sobrescreve
 * campos que vierem preenchidos do RM.
 */
abstract class Carga
{
    /** Cargas incrementais leem só o que mudou no RM desde a última execução gravada. */
    protected bool $incremental = false;

    /** @var array<string, array<string, int>> de-para em memória, por entidade */
    private static array $chaves = [];

    private array $totais = ['criados' => 0, 'atualizados' => 0, 'ignorados' => 0];

    public function __construct(
        protected Command $saida,
        protected int $coligada,
        protected bool $completa,
    ) {}

    /** Nome da carga: usado em --so, como entidade no de-para e na marca incremental. */
    abstract public static function nome(): string;

    abstract public function executar(): void;

    public function incremental(): bool
    {
        return $this->incremental;
    }

    public function chaveMarca(): string
    {
        return 'rm:sincronizar:'.static::nome().':'.$this->coligada;
    }

    public function totais(): array
    {
        return $this->totais;
    }

    protected function rm(): ConnectionInterface
    {
        return DB::connection('rm');
    }

    /**
     * Restringe a consulta às linhas criadas ou alteradas desde a última execução,
     * em qualquer uma das tabelas informadas (pelos aliases). Sem marca, não filtra.
     */
    protected function alteradoDesde(Builder $query, string ...$aliases): Builder
    {
        $desde = $this->incremental && ! $this->completa ? Cache::get($this->chaveMarca()) : null;

        if (! $desde) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($aliases, $desde) {
            foreach ($aliases as $alias) {
                $q->orWhereRaw("COALESCE({$alias}.RECMODIFIEDON, {$alias}.RECCREATEDON) >= ?", [$desde]);
            }
        });
    }

    // ----- De-para ----- //

    /** cod_* do HUB para uma chave RM; $entidade permite consultar o de-para de outra carga. */
    protected function codHub(string $chaveRm, ?string $entidade = null): ?int
    {
        $entidade ??= static::nome();

        self::$chaves[$entidade] ??= RmChave::where('entidade', $entidade)
            ->pluck('cod_hub', 'chave_rm')
            ->map(fn ($cod) => (int) $cod)
            ->all();

        return self::$chaves[$entidade][$chaveRm] ?? null;
    }

    protected function registrarChave(string $chaveRm, int $codHub, ?string $entidade = null): void
    {
        $entidade ??= static::nome();

        if ($this->codHub($chaveRm, $entidade) === $codHub) {
            return;
        }

        RmChave::updateOrCreate(['entidade' => $entidade, 'chave_rm' => $chaveRm], ['cod_hub' => $codHub]);

        self::$chaves[$entidade][$chaveRm] = $codHub;
    }

    /** cod_escola da coligada; a carga "escola" precisa ter rodado antes. */
    protected function codEscola(): int
    {
        return $this->codHub((string) $this->coligada, 'escola')
            ?? throw new RuntimeException("Coligada {$this->coligada} ainda não carregada: rode a carga escola.");
    }

    // ----- Gravação ----- //

    /**
     * @param  class-string<Model>  $model
     */
    protected function consulta(string $model): EloquentBuilder
    {
        $query = $model::query();

        return in_array(SoftDeletes::class, class_uses_recursive($model), true) ? $query->withTrashed() : $query;
    }

    /**
     * Registro do HUB já ligado à chave RM ou, se não houver, o que $novo devolver (normalmente
     * um model ainda não salvo). Devolve null, e registra o motivo, se ele foi excluído no HUB.
     *
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $escopo  colunas extras do PK composto (ex.: cod_curso da etapa)
     */
    protected function localizar(string $model, string $chaveRm, callable $novo, array $escopo = []): ?Model
    {
        $cod = $this->codHub($chaveRm);

        $registro = ($cod === null ? null : $this->consulta($model)->where($escopo)->whereKey($cod)->first()) ?? $novo();

        if (method_exists($registro, 'trashed') && $registro->trashed()) {
            $this->ignorar($chaveRm, 'excluído no HUB');

            return null;
        }

        return $registro;
    }

    /**
     * Código para um registro novo em tabela de PK atribuído: o próprio código RM quando
     * numérico e livre (facilita conferir), senão o próximo da tabela dentro do $escopo.
     *
     * @param  class-string<Model>  $model
     */
    protected function novoCodigo(string $model, string $coluna, int|string|null $sugestao, array $escopo = []): int
    {
        $query = $this->consulta($model)->where($escopo);

        if (is_numeric($sugestao) && (int) $sugestao > 0 && ! (clone $query)->where($coluna, (int) $sugestao)->exists()) {
            return (int) $sugestao;
        }

        return (int) $query->max($coluna) + 1;
    }

    /** Preenche só com valores não vazios, para não apagar no HUB o que falta no RM. */
    protected function preencher(Model $model, array $valores): void
    {
        $valores = array_map(fn ($valor) => is_string($valor) ? trim($valor) : $valor, $valores);

        $model->fill(array_filter($valores, fn ($valor) => $valor !== null && $valor !== ''));
    }

    protected function salvar(Model $model): void
    {
        if (! $model->exists) {
            $this->totais['criados']++;
        } elseif ($model->isDirty()) {
            $this->totais['atualizados']++;
        }

        $model->save();
    }

    /** Salva e liga o registro à chave RM no de-para da carga. */
    protected function gravar(Model $model, string $chaveRm): void
    {
        $this->salvar($model);
        $this->registrarChave($chaveRm, (int) $model->getKey());
    }

    /**
     * Cria ou atualiza a pessoa de uma origem RM (PPESSOA, ou FCFO no responsável financeiro).
     * Casa primeiro pelo de-para e depois pelo CPF, para não duplicar quem aparece nas duas origens.
     */
    protected function sincronizarPessoa(string $chaveRm, int|string|null $sugestao, ?string $cpf, array $dados): ?Pessoa
    {
        $cpf = strlen($digitos = $this->digitos($cpf)) === 11 ? $digitos : null;
        $cod = $this->codHub($chaveRm, 'pessoas');

        $pessoa = $cod === null ? null : Pessoa::withTrashed()->find($cod);
        $pessoa ??= $cpf ? Pessoa::withTrashed()->where('cpf', $cpf)->first() : null;

        if ($pessoa?->trashed()) {
            $this->ignorar("pessoa {$chaveRm}", "pessoa {$pessoa->cod_pessoa} excluída no HUB");

            return null;
        }

        if ($cpf && $pessoa && $pessoa->cpf !== $cpf && Pessoa::withTrashed()->where('cpf', $cpf)->exists()) {
            $this->ignorar("pessoa {$chaveRm}", "CPF {$cpf} já pertence a outra pessoa no HUB");

            return null;
        }

        $pessoa ??= new Pessoa(['cod_pessoa' => $this->novoCodigo(Pessoa::class, 'cod_pessoa', $sugestao)]);

        $this->preencher($pessoa, ['cpf' => $cpf] + $dados);
        $this->salvar($pessoa);
        $this->registrarChave($chaveRm, $pessoa->cod_pessoa, 'pessoas');

        return $pessoa;
    }

    // ----- Utilitários ----- //

    protected function ignorar(string $referencia, string $motivo): void
    {
        $this->totais['ignorados']++;
        $this->saida->line('<comment>['.static::nome()."] {$referencia} ignorado:</comment> {$motivo}");
    }

    protected function digitos(?string $valor): string
    {
        return preg_replace('/\D/', '', (string) $valor);
    }

    /** Texto aparado e cortado no tamanho da coluna do HUB; null quando vazio. */
    protected function texto(?string $valor, int $tamanho): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : mb_substr($valor, 0, $tamanho);
    }
}
