<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TurmaDisciplina extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_turma_disciplinas';

    protected $primaryKey = 'cod_turma_disciplina';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_turma_disciplina',
        'cod_turma',
        'cod_disciplina',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'cod_turma', 'cod_turma');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'cod_disciplina', 'cod_disciplina');
    }

    public function turmaDisciplinaFuncionarios(): HasMany
    {
        return $this->hasMany(TurmaDisciplinaFuncionario::class, 'cod_turma_disciplina', 'cod_turma_disciplina');
    }
}
