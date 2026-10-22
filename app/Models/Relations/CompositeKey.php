<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Model;

/**
 * Utilitários para comparar valores de chave composta entre models.
 */
final class CompositeKey
{
    /**
     * Valor da chave composta como texto. Os valores são convertidos para string porque o
     * driver do SQL Server pode devolver bigint como texto e o model pode guardá-lo como inteiro.
     *
     * @param  list<string>  $columns
     */
    public static function of(Model $model, array $columns): string
    {
        return implode('|', array_map(
            fn (string $column) => (string) $model->getAttribute($column),
            $columns,
        ));
    }

    /**
     * @param  list<string>  $columns
     */
    public static function hasNull(Model $model, array $columns): bool
    {
        foreach ($columns as $column) {
            if ($model->getAttribute($column) === null) {
                return true;
            }
        }

        return false;
    }
}
