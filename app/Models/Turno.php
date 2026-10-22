<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    use HasFactory;

    protected $table = 'edu_turnos';

    protected $primaryKey = 'cod_turno';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'cod_turno',
        'nome',
        'sigla',
    ];

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class, 'cod_turno', 'cod_turno');
    }
}
