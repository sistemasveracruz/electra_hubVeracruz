<?php

namespace App\Models;

use App\Models\Concerns\HasCompositeRelations;
use App\Models\Relations\BelongsToComposite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeCurricular extends Model
{
    use HasCompositeRelations, HasFactory, SoftDeletes;

    protected $table = 'edu_grades_curriculares';

    protected $primaryKey = 'cod_grade_curricular';

    protected $fillable = [
        'cod_grade',
        'cod_curso',
        'cod_etapa',
        'descricao',
        'data_inicio',
        'data_fim',
        'carga_horaria',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'datetime',
            'data_fim' => 'datetime',
            'carga_horaria' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cod_curso', 'cod_curso');
    }

    public function etapa(): BelongsToComposite
    {
        return $this->belongsToComposite(Etapa::class, ['cod_curso', 'cod_etapa'], ['cod_curso', 'cod_etapa']);
    }

    public function disciplinas(): HasMany
    {
        return $this->hasMany(GradeCurricularDisciplina::class, 'cod_grade_curricular', 'cod_grade_curricular');
    }

    public function aplicacoes(): HasMany
    {
        return $this->hasMany(AplicacaoCurricular::class, 'cod_grade_curricular', 'cod_grade_curricular');
    }
}
