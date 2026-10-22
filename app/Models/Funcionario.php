<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Funcionario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fun_funcionarios';

    protected $primaryKey = 'cod_funcionario';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_funcionario',
        'cod_pessoa',
        'tipo_cadastro',
        'professor_cadastro',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'professor_cadastro' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'cod_pessoa', 'cod_pessoa');
    }

    public function vinculos(): HasMany
    {
        return $this->hasMany(VinculoFuncionario::class, 'cod_funcionario', 'cod_funcionario');
    }

    public function turmaFuncionarios(): HasMany
    {
        return $this->hasMany(TurmaFuncionario::class, 'cod_funcionario', 'cod_funcionario');
    }

    public function turmaDisciplinaFuncionarios(): HasMany
    {
        return $this->hasMany(TurmaDisciplinaFuncionario::class, 'cod_funcionario', 'cod_funcionario');
    }
}
