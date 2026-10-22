<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuncaoTurma extends Model
{
    use HasFactory;

    protected $table = 'fun_funcoes_turma';

    protected $primaryKey = 'cod_funcao_turma';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descricao',
    ];

    public function turmaFuncionarios(): HasMany
    {
        return $this->hasMany(TurmaFuncionario::class, 'cod_funcao_turma', 'cod_funcao_turma');
    }
}
