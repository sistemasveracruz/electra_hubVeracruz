<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * "Pertence a" por chave composta, ex.: Turma (cod_curso, cod_etapa) → Etapa (cod_curso, cod_etapa).
 * Suporta lazy loading, eager loading (with) e has/whereHas. Não suporta associate()/dissociate().
 *
 * @template TRelatedModel of Model
 * @template TDeclaringModel of Model
 *
 * @extends Relation<TRelatedModel, TDeclaringModel, TRelatedModel|null>
 */
class BelongsToComposite extends Relation
{
    /**
     * @param  Builder<TRelatedModel>  $query
     * @param  TDeclaringModel  $child
     * @param  list<string>  $foreignKeys  Colunas no model que declara a relação.
     * @param  list<string>  $ownerKeys  Colunas correspondentes no model relacionado, na mesma ordem.
     */
    public function __construct(
        Builder $query,
        Model $child,
        protected array $foreignKeys,
        protected array $ownerKeys,
    ) {
        parent::__construct($query, $child);
    }

    public function addConstraints()
    {
        if (static::$constraints) {
            foreach ($this->foreignKeys as $i => $foreignKey) {
                $this->query->where(
                    $this->related->qualifyColumn($this->ownerKeys[$i]),
                    '=',
                    $this->parent->getAttribute($foreignKey),
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
                $this->related->qualifyColumn($this->ownerKeys[$i]),
                array_values(array_filter($this->getKeys($models, $foreignKey), fn ($valor) => $valor !== null)),
            );
        }
    }

    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }

    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[CompositeKey::of($result, $this->ownerKeys)] = $result;
        }

        foreach ($models as $model) {
            $chave = CompositeKey::of($model, $this->foreignKeys);

            if (isset($dictionary[$chave])) {
                $model->setRelation($relation, $dictionary[$chave]);
            }
        }

        return $models;
    }

    public function getResults()
    {
        if (CompositeKey::hasNull($this->parent, $this->foreignKeys)) {
            return null;
        }

        return $this->query->first();
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->select($columns);

        foreach ($this->foreignKeys as $i => $foreignKey) {
            $query->whereColumn(
                $query->qualifyColumn($this->ownerKeys[$i]),
                '=',
                $parentQuery->qualifyColumn($foreignKey),
            );
        }

        return $query;
    }
}
