<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodoLetivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_periodos_letivos';

    protected $primaryKey = 'cod_periodo_letivo';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_periodo_letivo',
        'cod_unidade_escolar',
        'cod_tipo_curso',
        'cod_periodo_letivo_anterior',
        'data_inicio',
        'data_fim',
        'data_estimada',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'datetime',
            'data_fim' => 'datetime',
            'data_estimada' => 'datetime',
            'ativo' => 'boolean',
        ];
    }

    public function unidadeEscolar(): BelongsTo
    {
        return $this->belongsTo(UnidadeEscolar::class, 'cod_unidade_escolar', 'cod_unidade_escolar');
    }

    public function tipoCurso(): BelongsTo
    {
        return $this->belongsTo(TipoCurso::class, 'cod_tipo_curso', 'cod_tipo_curso');
    }

    public function periodoAnterior(): BelongsTo
    {
        return $this->belongsTo(PeriodoLetivo::class, 'cod_periodo_letivo_anterior', 'cod_periodo_letivo');
    }

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class, 'cod_periodo_letivo', 'cod_periodo_letivo');
    }
}
