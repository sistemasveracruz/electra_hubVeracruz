<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pessoa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inst_pessoas';

    protected $primaryKey = 'cod_pessoa';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_pessoa',
        'cpf',
        'nome',
        'data_nascimento',
        'email',
        'email_pessoal',
        'email_alternativo',
        'telefone_fixo',
        'telefone_pessoal',
        'telefone_empresa',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'ativo' => 'boolean',
        ];
    }

    public function enderecos(): HasMany
    {
        return $this->hasMany(PessoaEndereco::class, 'cod_pessoa', 'cod_pessoa');
    }

    public function funcionarios(): HasMany
    {
        return $this->hasMany(Funcionario::class, 'cod_pessoa', 'cod_pessoa');
    }

    public function registrosAcademicos(): HasMany
    {
        return $this->hasMany(RegistroAcademico::class, 'cod_pessoa', 'cod_pessoa');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'cod_pessoa', 'cod_pessoa');
    }

    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class, 'cod_pessoa', 'cod_pessoa');
    }
}
