<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Familia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_familias';

    protected $primaryKey = 'cod_familia';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_familia',
        'cod_pessoa',
        'cod_pessoa_aluno',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'cod_pessoa', 'cod_pessoa');
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'cod_pessoa_aluno', 'cod_pessoa');
    }
}
