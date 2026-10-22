<?php

namespace Database\Factories;

use App\Models\Parentesco;
use App\Models\Status;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * Base das factories do domínio: Faker em pt_BR e geração das chaves primárias
 * manuais (colunas cod_* sem IDENTITY).
 *
 * @template TModel of Model
 *
 * @extends Factory<TModel>
 */
abstract class BaseFactory extends Factory
{
    /**
     * Próximo valor de chave por model, inicializado com o MAX da tabela na primeira utilização.
     *
     * @var array<class-string<Model>, int>
     */
    protected static array $nextKeys = [];

    protected function withFaker(): Generator
    {
        return fake('pt_BR');
    }

    /**
     * Próximo valor da chave primária manual do model desta factory.
     * Use como closure na definition para não consumir valores quando a chave for informada.
     */
    protected function nextKey(): int
    {
        $model = $this->modelName();
        $instance = new $model;

        static::$nextKeys[$model] ??= (int) $instance->newQueryWithoutScopes()->max($instance->getKeyName());

        return ++static::$nextKeys[$model];
    }

    protected function statusId(string $nome): int
    {
        return Status::query()->where('nome', $nome)->value('cod_status')
            ?? Status::factory()->create(['nome' => $nome])->cod_status;
    }

    protected function parentescoId(string $descricao): int
    {
        return Parentesco::query()->where('descricao', $descricao)->value('cod_parentesco')
            ?? Parentesco::factory()->create(['descricao' => $descricao])->cod_parentesco;
    }
}
