<?php

namespace App\Models;

use App\Models\Concerns\HasCompositeRelations;
use App\Models\Relations\BelongsToComposite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Turma extends Model
{
    use HasCompositeRelations, HasFactory, SoftDeletes;

    protected $table = 'edu_turmas';

    protected $primaryKey = 'cod_turma';

    protected $fillable = [
        'codigo_turma',
        'cod_unidade_escolar',
        'cod_periodo_letivo',
        'cod_curso',
        'cod_etapa',
        'cod_sala',
        'cod_turno',
        'nome',
        'nome_reduzido',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function unidadeEscolar(): BelongsTo
    {
        return $this->belongsTo(UnidadeEscolar::class, 'cod_unidade_escolar', 'cod_unidade_escolar');
    }

    public function periodoLetivo(): BelongsTo
    {
        return $this->belongsTo(PeriodoLetivo::class, 'cod_periodo_letivo', 'cod_periodo_letivo');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cod_curso', 'cod_curso');
    }

    public function etapa(): BelongsToComposite
    {
        return $this->belongsToComposite(Etapa::class, ['cod_curso', 'cod_etapa'], ['cod_curso', 'cod_etapa']);
    }

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class, 'cod_sala', 'cod_sala');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'cod_turno', 'cod_turno');
    }

    public function turmaDisciplinas(): HasMany
    {
        return $this->hasMany(TurmaDisciplina::class, 'cod_turma', 'cod_turma');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'cod_turma', 'cod_turma');
    }

    public function enturmas(): HasMany
    {
        return $this->hasMany(Enturma::class, 'cod_turma', 'cod_turma');
    }

    public function turmaFuncionarios(): HasMany
    {
        return $this->hasMany(TurmaFuncionario::class, 'cod_turma', 'cod_turma');
    }
}
