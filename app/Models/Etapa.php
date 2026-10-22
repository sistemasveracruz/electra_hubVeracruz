<?php

namespace App\Models;

use App\Models\Concerns\HasCompositeRelations;
use App\Models\Relations\HasManyComposite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Etapa extends Model
{
    use HasCompositeRelations, HasFactory, SoftDeletes;

    protected $table = 'edu_etapas';

    /**
     * Chave primária composta (cod_curso, cod_etapa).
     * O Eloquent só aceita uma coluna em $primaryKey; as consultas de
     * save/delete/refresh são completadas com cod_curso nos métodos abaixo.
     */
    protected $primaryKey = 'cod_etapa';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_etapa',
        'cod_curso',
        'nome',
        'sigla',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    protected function setKeysForSelectQuery($query)
    {
        return parent::setKeysForSelectQuery($query)
            ->where('cod_curso', '=', $this->original['cod_curso'] ?? $this->getAttribute('cod_curso'));
    }

    protected function setKeysForSaveQuery($query)
    {
        return parent::setKeysForSaveQuery($query)
            ->where('cod_curso', '=', $this->original['cod_curso'] ?? $this->getAttribute('cod_curso'));
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cod_curso', 'cod_curso');
    }

    public function turmas(): HasManyComposite
    {
        return $this->hasManyComposite(Turma::class, ['cod_curso', 'cod_etapa'], ['cod_curso', 'cod_etapa']);
    }

    public function matriculas(): HasManyComposite
    {
        return $this->hasManyComposite(Matricula::class, ['cod_curso', 'cod_etapa'], ['cod_curso', 'cod_etapa']);
    }

    public function gradesCurriculares(): HasManyComposite
    {
        return $this->hasManyComposite(GradeCurricular::class, ['cod_curso', 'cod_etapa'], ['cod_curso', 'cod_etapa']);
    }

    public function gradeDisciplinas(): HasManyComposite
    {
        return $this->hasManyComposite(GradeCurricularDisciplina::class, ['cod_curso', 'cod_etapa'], ['cod_curso', 'cod_etapa']);
    }

    public function aplicacoesCurriculares(): HasManyComposite
    {
        return $this->hasManyComposite(AplicacaoCurricular::class, ['cod_curso', 'cod_etapa'], ['cod_curso', 'cod_etapa']);
    }
}
