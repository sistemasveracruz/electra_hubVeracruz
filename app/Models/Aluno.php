<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aluno extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_alunos';

    protected $primaryKey = 'cod_aluno';

    protected $fillable = [
        'cod_pessoa',
        'cod_registro_academico',
        'cod_matricula',
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

    public function registroAcademico(): BelongsTo
    {
        return $this->belongsTo(RegistroAcademico::class, 'cod_registro_academico', 'cod_registro_academico');
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'cod_matricula', 'cod_matricula');
    }

    public function enturmas(): HasMany
    {
        return $this->hasMany(Enturma::class, 'cod_aluno', 'cod_aluno');
    }

    public function responsaveis(): HasMany
    {
        return $this->hasMany(Responsavel::class, 'cod_aluno', 'cod_aluno');
    }
}
