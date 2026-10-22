<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadeEscolar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inst_unidades_escolares';

    protected $primaryKey = 'cod_unidade_escolar';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_unidade_escolar',
        'cod_escola',
        'nome',
        'sigla',
        'descricao',
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

    public function salas(): HasMany
    {
        return $this->hasMany(Sala::class, 'cod_unidade_escolar', 'cod_unidade_escolar');
    }

    public function periodosLetivos(): HasMany
    {
        return $this->hasMany(PeriodoLetivo::class, 'cod_unidade_escolar', 'cod_unidade_escolar');
    }

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class, 'cod_unidade_escolar', 'cod_unidade_escolar');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'cod_unidade_escolar', 'cod_unidade_escolar');
    }
}
