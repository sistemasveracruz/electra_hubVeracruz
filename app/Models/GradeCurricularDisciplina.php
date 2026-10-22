<?php

namespace App\Models;

use App\Models\Concerns\HasCompositeRelations;
use App\Models\Relations\BelongsToComposite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeCurricularDisciplina extends Model
{
    use HasCompositeRelations, HasFactory;

    protected $table = 'edu_grade_curricular_disciplinas';

    protected $primaryKey = 'cod_grade_disciplina';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'cod_grade_disciplina',
        'cod_grade_curricular',
        'cod_curso',
        'cod_etapa',
        'cod_disciplina',
        'cod_turno',
        'periodo_grade',
        'aulas_semanais',
        'quantidade_semanas',
        'carga_horaria',
        'obrigatoria',
    ];

    protected function casts(): array
    {
        return [
            'aulas_semanais' => 'decimal:2',
            'quantidade_semanas' => 'decimal:2',
            'carga_horaria' => 'decimal:2',
            'obrigatoria' => 'boolean',
        ];
    }

    public function gradeCurricular(): BelongsTo
    {
        return $this->belongsTo(GradeCurricular::class, 'cod_grade_curricular', 'cod_grade_curricular');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cod_curso', 'cod_curso');
    }

    public function etapa(): BelongsToComposite
    {
        return $this->belongsToComposite(Etapa::class, ['cod_curso', 'cod_etapa'], ['cod_curso', 'cod_etapa']);
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'cod_disciplina', 'cod_disciplina');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'cod_turno', 'cod_turno');
    }
}
