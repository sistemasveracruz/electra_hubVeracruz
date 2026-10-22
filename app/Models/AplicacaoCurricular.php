<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AplicacaoCurricular extends Model
{
    use Compoships, HasFactory;

    protected $table = 'edu_aplicacoes_curriculares';

    protected $primaryKey = 'cod_aplicacao_curricular';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'cod_aplicacao_curricular',
        'cod_grade_curricular',
        'cod_unidade_escolar',
        'cod_curso',
        'cod_etapa',
        'cod_turno',
        'regime_grade',
        'periodo_grade',
        'data_inicio',
        'data_fim',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'datetime',
            'data_fim' => 'datetime',
        ];
    }

    public function gradeCurricular(): BelongsTo
    {
        return $this->belongsTo(GradeCurricular::class, 'cod_grade_curricular', 'cod_grade_curricular');
    }

    public function unidadeEscolar(): BelongsTo
    {
        return $this->belongsTo(UnidadeEscolar::class, 'cod_unidade_escolar', 'cod_unidade_escolar');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cod_curso', 'cod_curso');
    }

    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class, ['cod_curso', 'cod_etapa'], ['cod_curso', 'cod_etapa']);
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'cod_turno', 'cod_turno');
    }
}
