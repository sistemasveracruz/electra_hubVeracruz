<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_cursos';

    protected $primaryKey = 'cod_curso';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_curso',
        'cod_escola',
        'nome',
        'sigla',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'cod_escola', 'cod_escola');
    }

    public function etapas(): HasMany
    {
        return $this->hasMany(Etapa::class, 'cod_curso', 'cod_curso');
    }

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class, 'cod_curso', 'cod_curso');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'cod_curso', 'cod_curso');
    }

    public function gradesCurriculares(): HasMany
    {
        return $this->hasMany(GradeCurricular::class, 'cod_curso', 'cod_curso');
    }
}
