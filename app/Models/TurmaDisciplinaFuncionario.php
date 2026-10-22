<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TurmaDisciplinaFuncionario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fun_turma_disciplina_funcionarios';

    protected $primaryKey = 'cod_turma_disciplina_funcionario';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_turma_disciplina_funcionario',
        'cod_turma_disciplina',
        'cod_funcionario',
        'tipo_atribuicao',
        'codigo_papel_origem',
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

    public function turmaDisciplina(): BelongsTo
    {
        return $this->belongsTo(TurmaDisciplina::class, 'cod_turma_disciplina', 'cod_turma_disciplina');
    }

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'cod_funcionario', 'cod_funcionario');
    }
}
