<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * De-para de uma chave do TOTVS RM para o cod_* correspondente no HUB.
 * entidade = nome da carga (ex.: "cursos"); chave_rm = chave RM serializada (ex.: "1|EF").
 */
class RmChave extends Model
{
    protected $table = 'aux_rm_chaves';

    protected $primaryKey = 'cod_rm_chave';

    protected $fillable = [
        'entidade',
        'chave_rm',
        'cod_hub',
    ];

    protected function casts(): array
    {
        return [
            'cod_hub' => 'integer',
        ];
    }
}
