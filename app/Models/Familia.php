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

    /**
     * Chave primária composta (cod_familia, cod_pessoa, cod_pessoa_aluno).
     * O Eloquent só aceita uma coluna em $primaryKey; as consultas de
     * save/delete/refresh são completadas com as demais colunas nos métodos abaixo.
     */
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

    protected function setKeysForSelectQuery($query)
    {
        return parent::setKeysForSelectQuery($query)
            ->where('cod_pessoa', '=', $this->original['cod_pessoa'] ?? $this->getAttribute('cod_pessoa'))
            ->where('cod_pessoa_aluno', '=', $this->original['cod_pessoa_aluno'] ?? $this->getAttribute('cod_pessoa_aluno'));
    }

    protected function setKeysForSaveQuery($query)
    {
        return parent::setKeysForSaveQuery($query)
            ->where('cod_pessoa', '=', $this->original['cod_pessoa'] ?? $this->getAttribute('cod_pessoa'))
            ->where('cod_pessoa_aluno', '=', $this->original['cod_pessoa_aluno'] ?? $this->getAttribute('cod_pessoa_aluno'));
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
