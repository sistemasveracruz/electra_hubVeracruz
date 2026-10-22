<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistroAcademico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_registros_academicos';

    protected $primaryKey = 'cod_registro_academico';

    protected $fillable = [
        'cod_pessoa',
        'cod_escola',
        'registro_academico',
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

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'cod_registro_academico', 'cod_registro_academico');
    }

    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class, 'cod_registro_academico', 'cod_registro_academico');
    }
}
