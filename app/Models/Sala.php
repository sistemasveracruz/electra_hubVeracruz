<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sala extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_salas';

    protected $primaryKey = 'cod_sala';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_sala',
        'cod_unidade_escolar',
        'cod_tipo_sala',
        'nome',
        'descricao',
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

    public function tipoSala(): BelongsTo
    {
        return $this->belongsTo(TipoSala::class, 'cod_tipo_sala', 'cod_tipo_sala');
    }

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class, 'cod_sala', 'cod_sala');
    }
}
