<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoCurso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_tipo_cursos';

    protected $primaryKey = 'cod_tipo_curso';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_tipo_curso',
        'cod_escola',
        'nome',
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

    public function periodosLetivos(): HasMany
    {
        return $this->hasMany(PeriodoLetivo::class, 'cod_tipo_curso', 'cod_tipo_curso');
    }
}
