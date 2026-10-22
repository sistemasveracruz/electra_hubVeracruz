<?php

namespace Database\Factories;

use App\Models\Escola;
use App\Models\Pessoa;
use App\Models\RegistroAcademico;
use Database\Seeders\Support\Catalogo;

/**
 * @extends BaseFactory<RegistroAcademico>
 */
class RegistroAcademicoFactory extends BaseFactory
{
    protected $model = RegistroAcademico::class;

    public function definition(): array
    {
        return [
            'cod_pessoa' => Pessoa::factory()->aluno(10),
            'cod_escola' => Escola::factory(),
            // Ano de ingresso + sequencial (9 caracteres; a coluna aceita 12).
            'registro_academico' => $this->faker->unique()->numerify(Catalogo::ANO_LETIVO_ATUAL.'#####'),
            'ativo' => true,
        ];
    }
}
