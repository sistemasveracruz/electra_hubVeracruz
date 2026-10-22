<?php

namespace App\Models\Concerns;

use App\Models\Relations\BelongsToComposite;
use App\Models\Relations\HasManyComposite;
use Illuminate\Database\Eloquent\Model;

/**
 * Relações por chave composta (ex.: cod_curso + cod_etapa), que o Eloquent não oferece nativamente.
 */
trait HasCompositeRelations
{
    /**
     * @template TRelatedModel of Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @param  list<string>  $foreignKeys  Colunas neste model.
     * @param  list<string>  $ownerKeys  Colunas correspondentes no model relacionado.
     * @return BelongsToComposite<TRelatedModel, $this>
     */
    protected function belongsToComposite(string $related, array $foreignKeys, array $ownerKeys): BelongsToComposite
    {
        $instance = $this->newRelatedInstance($related);

        return new BelongsToComposite($instance->newQuery(), $this, $foreignKeys, $ownerKeys);
    }

    /**
     * @template TRelatedModel of Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @param  list<string>  $foreignKeys  Colunas no model relacionado.
     * @param  list<string>  $localKeys  Colunas correspondentes neste model.
     * @return HasManyComposite<TRelatedModel, $this>
     */
    protected function hasManyComposite(string $related, array $foreignKeys, array $localKeys): HasManyComposite
    {
        $instance = $this->newRelatedInstance($related);

        return new HasManyComposite($instance->newQuery(), $this, $foreignKeys, $localKeys);
    }
}
