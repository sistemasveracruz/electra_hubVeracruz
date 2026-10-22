<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Matricula extends Model
{
    use Compoships, HasFactory, SoftDeletes;

    protected $table = 'edu_matriculas';

    protected $primaryKey = 'cod_matricula';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_matricula',
        'cod_pessoa',
        'cod_escola',
        'cod_unidade_escolar',
        'cod_curso',
        'cod_etapa',
        'cod_turma',
        'cod_status',
        'cod_registro_academico',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'cod_pessoa', 'cod_pessoa');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'cod_escola', 'cod_escola');
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

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'cod_turma', 'cod_turma');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'cod_status', 'cod_status');
    }

    public function registroAcademico(): BelongsTo
    {
        return $this->belongsTo(RegistroAcademico::class, 'cod_registro_academico', 'cod_registro_academico');
    }

    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class, 'cod_matricula', 'cod_matricula');
    }

    public function enturmas(): HasMany
    {
        return $this->hasMany(Enturma::class, 'cod_matricula', 'cod_matricula');
    }
}
