<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Escola extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inst_escolas';

    protected $primaryKey = 'cod_escola';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_escola',
        'nome',
        'nome_fantasia',
        'cnpj',
        'descricao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function unidadesEscolares(): HasMany
    {
        return $this->hasMany(UnidadeEscolar::class, 'cod_escola', 'cod_escola');
    }

    public function tipoCursos(): HasMany
    {
        return $this->hasMany(TipoCurso::class, 'cod_escola', 'cod_escola');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'cod_escola', 'cod_escola');
    }

    public function registrosAcademicos(): HasMany
    {
        return $this->hasMany(RegistroAcademico::class, 'cod_escola', 'cod_escola');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'cod_escola', 'cod_escola');
    }

    public function vinculosFuncionarios(): HasMany
    {
        return $this->hasMany(VinculoFuncionario::class, 'cod_escola', 'cod_escola');
    }
}
