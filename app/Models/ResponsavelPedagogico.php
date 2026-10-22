<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResponsavelPedagogico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_responsaveis_pedagogico';

    protected $primaryKey = 'cod_responsavel_pedagogico';

    protected $fillable = [
        'cod_pessoa_aluno',
        'cod_pessoa_responsavel',
        'cod_parentesco_responsavel',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'cod_pessoa_aluno', 'cod_pessoa');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'cod_pessoa_responsavel', 'cod_pessoa');
    }

    public function parentesco(): BelongsTo
    {
        return $this->belongsTo(Parentesco::class, 'cod_parentesco_responsavel', 'cod_parentesco');
    }
}
