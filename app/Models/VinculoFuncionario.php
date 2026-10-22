<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VinculoFuncionario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fun_vinculos_funcionarios';

    protected $primaryKey = 'cod_vinculo_funcionario';

    protected $fillable = [
        'cod_funcionario',
        'cod_escola',
        'cod_unidade_escolar',
        'cargo',
        'setor',
        'tipo_vinculo',
        'data_admissao',
        'data_desligamento',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_admissao' => 'datetime',
            'data_desligamento' => 'datetime',
            'ativo' => 'boolean',
        ];
    }

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'cod_funcionario', 'cod_funcionario');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'cod_escola', 'cod_escola');
    }

    public function unidadeEscolar(): BelongsTo
    {
        return $this->belongsTo(UnidadeEscolar::class, 'cod_unidade_escolar', 'cod_unidade_escolar');
    }
}
