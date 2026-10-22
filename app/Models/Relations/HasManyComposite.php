<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * "Tem muitos" por chave composta, ex.: Etapa (cod_curso, cod_etapa) → Turmas (cod_curso, cod_etapa).
 * Suporta lazy loading, eager loading (with) e has/whereHas. Não suporta create()/save() pela relação.
 *
 * @template TRelatedModel of Model
 * @template TDeclaringModel of Model
 *
 * @extends Relation<TRelatedModel, TDeclaringModel, Collection<int, TRelatedModel>>
 */
class HasManyComposite extends Relation
{
    /**
     * @param  Builder<TRelatedModel>  $query
     * @param  TDeclaringModel  $parent
     * @param  list<string>  $foreignKeys  Colunas no model relacionado.
     * @param  list<string>  $localKeys  Colunas correspondentes no model que declara a relação, na mesma ordem.
     */
    public function __construct(
        Builder $query,
        Model $parent,
        protected array $foreignKeys,
        protected array $localKeys,
    ) {
        parent::__construct($query, $parent);
    }

    public function addConstraints()
    {
        if (static::$constraints) {
            foreach ($this->foreignKeys as $i => $foreignKey) {
                $this->query->where(
                    $this->related->qualifyColumn($foreignKey),
                    '=',
                    $this->parent->getAttribute($this->localKeys[$i]),
                );
            }
        }
    }

    /**
     * Filtra cada coluna pelos valores dos models (um superconjunto); o par exato é resolvido em match().
     */
    public function addEagerConstraints(array $models)
    {
        foreach ($this->foreignKeys as $i => $foreignKey) {
            $this->query->whereIn(
                $this->related->qualifyColumn($foreignKey),
                array_values(array_filter($this->getKeys($models, $this->localKeys[$i]), fn ($valor) => $valor !== null)),
            );
        }
    }

    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[CompositeKey::of($result, $this->foreignKeys)][] = $result;
        }

        foreach ($models as $model) {
            $model->setRelation(
                $relation,
                $this->related->newCollection($dictionary[CompositeKey::of($model, $this->localKeys)] ?? []),
            );
        }

        return $models;
    }

    public function getResults()
    {
        if (CompositeKey::hasNull($this->parent, $this->localKeys)) {
            return $this->related->newCollection();
        }

        return $this->query->get();
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->select($columns);

        foreach ($this->foreignKeys as $i => $foreignKey) {
            $query->whereColumn(
                $query->qualifyColumn($foreignKey),
                '=',
                $parentQuery->qualifyColumn($this->localKeys[$i]),
            );
        }

        return $query;
    }
}
