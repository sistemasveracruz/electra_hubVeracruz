<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Responsavel extends Model
{
    use HasFactory;

    protected $table = 'edu_responsaveis';

    /**
     * Tabela com chave primária composta
     * (cod_aluno, cod_responsavel_pedagogico, cod_responsavel_financeiro).
     * O Eloquent não oferece suporte nativo a chaves compostas, por isso
     * a chave incremental é desabilitada.
     */
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_aluno',
        'cod_pessoa_aluno',
        'cod_responsavel_pedagogico',
        'cod_responsavel_financeiro',
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'cod_aluno', 'cod_aluno');
    }

    public function pessoaAluno(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'cod_pessoa_aluno', 'cod_pessoa');
    }

    public function responsavelPedagogico(): BelongsTo
    {
        return $this->belongsTo(ResponsavelPedagogico::class, 'cod_responsavel_pedagogico', 'cod_responsavel_pedagogico');
    }

    public function responsavelFinanceiro(): BelongsTo
    {
        return $this->belongsTo(ResponsavelFinanceiro::class, 'cod_responsavel_financeiro', 'cod_responsavel_financeiro');
    }
}
