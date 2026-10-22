<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enturma extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_enturmas';

    protected $primaryKey = 'cod_enturma';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_enturma',
        'cod_aluno',
        'cod_matricula',
        'cod_turma',
        'cod_status',
        'numero_chamada',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'cod_aluno', 'cod_aluno');
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'cod_matricula', 'cod_matricula');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'cod_turma', 'cod_turma');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'cod_status', 'cod_status');
    }
}
