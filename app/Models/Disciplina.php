<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disciplina extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_disciplinas';

    protected $primaryKey = 'cod_disciplina';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_disciplina',
        'nome',
        'sigla',
        'observacoes',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function turmaDisciplinas(): HasMany
    {
        return $this->hasMany(TurmaDisciplina::class, 'cod_disciplina', 'cod_disciplina');
    }

    public function gradeDisciplinas(): HasMany
    {
        return $this->hasMany(GradeCurricularDisciplina::class, 'cod_disciplina', 'cod_disciplina');
    }
}
