<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\FuncaoTurma;
use App\Models\Parentesco;
use App\Models\PeriodoLetivo;
use App\Models\Status;
use App\Models\TipoSala;
use App\Models\Turma;
use App\Models\Turno;
use App\Models\UnidadeEscolar;
use Closure;
use Database\Seeders\Support\Catalogo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Base dos seeders de desenvolvimento.
 *
 * - Não roda em produção.
 * - Cada seeder executa dentro de uma transação.
 * - Registros são localizados por chave natural e só criados quando não existem:
 *   nada é atualizado, excluído ou truncado, e nenhum ID fixo é presumido.
 */
abstract class DevSeeder extends Seeder
{
    /** @var array<string, Model> */
    private array $cache = [];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn(static::class.' ignorado: seeders de desenvolvimento não rodam em produção.');

            return;
        }

        DB::transaction(fn () => $this->seed());
    }

    abstract protected function seed(): void;

    /**
     * Retorna o registro que corresponde à chave natural ou o cria pela factory do model.
     * Registros existentes (inclusive soft-deleted) nunca são alterados.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  array<string, mixed>  $chave
     * @param  array<string, mixed>  $atributos
     * @return TModel
     */
    protected function firstOrCreate(string $model, array $chave, array $atributos = []): Model
    {
        $query = $model::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
            $query->withTrashed();
        }

        return $query->where($chave)->first()
            ?? $model::factory()->create($chave + $atributos);
    }

    // ----- Consultas por chave natural (registros criados por seeders anteriores) ----- //

    protected function escola(): Escola
    {
        return $this->remember('escola', fn () => Escola::query()
            ->where('cnpj', Catalogo::escolaCnpj())
            ->firstOrFail());
    }

    protected function unidade(string $sigla): UnidadeEscolar
    {
        return $this->remember("unidade:{$sigla}", fn () => UnidadeEscolar::query()
            ->where('cod_escola', $this->escola()->cod_escola)
            ->where('sigla', $sigla)
            ->firstOrFail());
    }

    protected function curso(string $sigla): Curso
    {
        return $this->remember("curso:{$sigla}", fn () => Curso::query()
            ->where('cod_escola', $this->escola()->cod_escola)
            ->where('sigla', $sigla)
            ->firstOrFail());
    }

    protected function turno(string $sigla): Turno
    {
        return $this->remember("turno:{$sigla}", fn () => Turno::query()->where('sigla', $sigla)->firstOrFail());
    }

    protected function status(string $nome): Status
    {
        return $this->remember("status:{$nome}", fn () => Status::query()->where('nome', $nome)->firstOrFail());
    }

    protected function disciplina(string $sigla): Disciplina
    {
        return $this->remember("disciplina:{$sigla}", fn () => Disciplina::query()->where('sigla', $sigla)->firstOrFail());
    }

    protected function tipoSala(string $nome): TipoSala
    {
        return $this->remember("tipo_sala:{$nome}", fn () => TipoSala::query()->where('nome', $nome)->firstOrFail());
    }

    protected function parentesco(string $descricao): Parentesco
    {
        return $this->remember("parentesco:{$descricao}", fn () => Parentesco::query()->where('descricao', $descricao)->firstOrFail());
    }

    protected function funcaoTurma(string $codigo): FuncaoTurma
    {
        return $this->remember("funcao:{$codigo}", fn () => FuncaoTurma::query()->where('codigo', $codigo)->firstOrFail());
    }

    protected function periodo(string $siglaUnidade, int $ano): PeriodoLetivo
    {
        return $this->remember("periodo:{$siglaUnidade}:{$ano}", fn () => PeriodoLetivo::query()
            ->where('cod_unidade_escolar', $this->unidade($siglaUnidade)->cod_unidade_escolar)
            ->whereYear('data_inicio', $ano)
            ->firstOrFail());
    }

    protected function turma(int $ano, string $siglaCurso, string $codigo): Turma
    {
        return $this->remember("turma:{$ano}:{$siglaCurso}:{$codigo}", fn () => Turma::query()
            ->where('cod_periodo_letivo', $this->periodo(Catalogo::unidadeDoCurso($siglaCurso), $ano)->cod_periodo_letivo)
            ->where('codigo_turma', Catalogo::codigoTurma($siglaCurso, $codigo))
            ->firstOrFail());
    }

    /**
     * @template TModel of Model
     *
     * @param  Closure(): TModel  $resolver
     * @return TModel
     */
    private function remember(string $chave, Closure $resolver): Model
    {
        return $this->cache[$chave] ??= $resolver();
    }
}
