<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoSala extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'edu_tipo_salas';

    protected $primaryKey = 'cod_tipo_sala';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'cod_tipo_sala',
        'nome',
        'permite_reserva',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'permite_reserva' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function salas(): HasMany
    {
        return $this->hasMany(Sala::class, 'cod_tipo_sala', 'cod_tipo_sala');
    }
}
