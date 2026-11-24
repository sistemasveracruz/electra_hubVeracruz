<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeCurricularDisciplina extends Model
{
    use HasFactory;

    protected $table = 'edu_grade_curricular_disciplinas';

    protected $primaryKey = 'cod_grade_disciplina';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    /**
     * Sem cod_curso, a etapa só é identificável pela grade (gradeCurricular->etapa).
     */
    protected $fillable = [
        'cod_grade_disciplina',
        'cod_grade_curricular',
        'cod_etapa',
        'cod_disciplina',
        'periodo_grade',
        'aulas_semanais',
        'carga_horaria',
        'obrigatoria',
    ];

    protected function casts(): array
    {
        return [
            'aulas_semanais' => 'decimal:2',
            'carga_horaria' => 'decimal:2',
            'obrigatoria' => 'boolean',
        ];
    }

    public function gradeCurricular(): BelongsTo
    {
        return $this->belongsTo(GradeCurricular::class, 'cod_grade_curricular', 'cod_grade_curricular');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'cod_disciplina', 'cod_disciplina');
    }
}
