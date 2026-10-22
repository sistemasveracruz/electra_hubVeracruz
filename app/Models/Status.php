<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_status';

    protected $primaryKey = 'cod_status';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_status',
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

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'cod_status', 'cod_status');
    }

    public function enturmas(): HasMany
    {
        return $this->hasMany(Enturma::class, 'cod_status', 'cod_status');
    }
}
