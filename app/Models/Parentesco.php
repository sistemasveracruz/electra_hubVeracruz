<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parentesco extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'aux_parentesco';

    protected $primaryKey = 'cod_parentesco';

    protected $fillable = [
        'descricao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function responsaveisFinanceiros(): HasMany
    {
        return $this->hasMany(ResponsavelFinanceiro::class, 'cod_parentesco_responsavel', 'cod_parentesco');
    }

    public function responsaveisPedagogico(): HasMany
    {
        return $this->hasMany(ResponsavelPedagogico::class, 'cod_parentesco_responsavel', 'cod_parentesco');
    }
}
