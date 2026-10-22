<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PessoaEndereco extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inst_pessoa_enderecos';

    protected $primaryKey = 'cod_pessoa_endereco';

    protected $fillable = [
        'cod_pessoa',
        'tipo_endereco',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
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
}
