<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TurmaFuncionario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fun_turma_funcionarios';

    protected $primaryKey = 'cod_turma_funcionario';

    protected $fillable = [
        'cod_turma',
        'cod_funcionario',
        'cod_funcao_turma',
        'data_inicio',
        'data_fim',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'datetime',
            'data_fim' => 'datetime',
            'ativo' => 'boolean',
        ];
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'cod_turma', 'cod_turma');
    }

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'cod_funcionario', 'cod_funcionario');
    }

    public function funcaoTurma(): BelongsTo
    {
        return $this->belongsTo(FuncaoTurma::class, 'cod_funcao_turma', 'cod_funcao_turma');
    }
}
