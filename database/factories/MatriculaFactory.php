<?php

namespace Database\Factories;

use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\RegistroAcademico;
use App\Models\Turma;
use Database\Seeders\Support\Catalogo;

/**
 * Escola, unidade, curso e etapa são derivados da turma; o registro acadêmico é
 * o da pessoa na escola (reaproveitado se já existir).
 *
 * @extends BaseFactory<Matricula>
 */
class MatriculaFactory extends BaseFactory
{
    protected $model = Matricula::class;

    public function definition(): array
    {
        return [
            'cod_matricula' => fn () => $this->nextKey(),
            'cod_pessoa' => Pessoa::factory()->aluno(10),
            'cod_turma' => Turma::factory(),
            'cod_escola' => fn (array $atributos) => $this->turma($atributos)->unidadeEscolar->cod_escola,
            'cod_unidade_escolar' => fn (array $atributos) => $this->turma($atributos)->cod_unidade_escolar,
            'cod_curso' => fn (array $atributos) => $this->turma($atributos)->cod_curso,
            'cod_etapa' => fn (array $atributos) => $this->turma($atributos)->cod_etapa,
            'cod_status' => fn () => $this->statusId(Catalogo::STATUS_MATRICULADO),
            'cod_registro_academico' => fn (array $atributos) => $this->registroAcademico(
                (int) $atributos['cod_pessoa'],
                (int) $atributos['cod_escola'],
            ),
            'ativo' => true,
        ];
    }

    public function matriculada(): static
    {
        return $this->comStatus(Catalogo::STATUS_MATRICULADO);
    }

    public function trancada(): static
    {
        return $this->comStatus(Catalogo::STATUS_TRANCADO);
    }

    public function transferida(): static
    {
        return $this->comStatus(Catalogo::STATUS_TRANSFERIDO)->state(['ativo' => false]);
    }

    public function cancelada(): static
    {
        return $this->comStatus(Catalogo::STATUS_CANCELADO)->state(['ativo' => false]);
    }

    public function concluida(): static
    {
        return $this->comStatus(Catalogo::STATUS_CONCLUIDO);
    }

    private function comStatus(string $nome): static
    {
        return $this->state(fn () => ['cod_status' => $this->statusId($nome)]);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function turma(array $atributos): Turma
    {
        return Turma::query()->findOrFail($atributos['cod_turma']);
    }

    private function registroAcademico(int $codPessoa, int $codEscola): int
    {
        return RegistroAcademico::query()
            ->where('cod_pessoa', $codPessoa)
            ->where('cod_escola', $codEscola)
            ->value('cod_registro_academico')
            ?? RegistroAcademico::factory()->create([
                'cod_pessoa' => $codPessoa,
                'cod_escola' => $codEscola,
            ])->cod_registro_academico;
    }
}
