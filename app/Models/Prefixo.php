<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prefixo extends Model
{
    use HasFactory;

    protected $table = 'doc_prefixos';

    protected $primaryKey = 'cod_prefixo';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'cod_prefixo',
        'sigla',
        'descricao',
    ];
}
