<?php

namespace Database\Factories;

use App\Models\Pessoa;
use Database\Seeders\Support\DevData;

/**
 * @extends BaseFactory<Pessoa>
 */
class PessoaFactory extends BaseFactory
{
    protected $model = Pessoa::class;

    public function definition(): array
    {
        $nome = $this->faker->firstName().' '.$this->faker->lastName().' '.$this->faker->lastName();

        return [
            'cod_pessoa' => fn () => $this->nextKey(),
            'cpf' => $this->faker->unique()->cpf(false),
            'nome' => $nome,
            'data_nascimento' => DevData::dataNascimento($this->faker->numberBetween(25, 60)),
            'email' => DevData::email($nome),
            'email_pessoal' => null,
            'email_alternativo' => null,
            'telefone_fixo' => $this->faker->optional(0.3)->landlineNumber(),
            'telefone_pessoal' => $this->faker->cellphoneNumber(),
            'telefone_empresa' => null,
            'ativo' => true,
        ];
    }

    /**
     * Pessoa adulta com idade entre os limites informados.
     */
    public function adulto(int $idadeMinima = 28, int $idadeMaxima = 60): static
    {
        return $this->state(fn () => [
            'data_nascimento' => DevData::dataNascimento($this->faker->numberBetween($idadeMinima, $idadeMaxima)),
        ]);
    }

    /**
     * Aluno com a idade de referência da etapa; crianças não têm e-mail nem celular próprios.
     */
    public function aluno(int $idade): static
    {
        return $this->state(fn () => [
            'data_nascimento' => DevData::dataNascimento($idade),
            'email' => $idade >= 15 ? $this->faker->safeEmail() : null,
            'telefone_fixo' => null,
            'telefone_pessoal' => $idade >= 15 ? $this->faker->cellphoneNumber() : null,
        ]);
    }

    public function inativa(): static
    {
        return $this->state(['ativo' => false]);
    }
}
