<?php

namespace Database\Factories;

use App\Models\Pessoa;
use App\Models\PessoaEndereco;
use Database\Seeders\Support\DevData;

/**
 * @extends BaseFactory<PessoaEndereco>
 */
class PessoaEnderecoFactory extends BaseFactory
{
    protected $model = PessoaEndereco::class;

    public function definition(): array
    {
        return [
            'cod_pessoa' => Pessoa::factory(),
            'tipo_endereco' => 'PESSOAL',
            ...DevData::endereco(),
            'ativo' => true,
        ];
    }

    public function pessoal(): static
    {
        return $this->state(['tipo_endereco' => 'PESSOAL']);
    }

    public function empresarial(): static
    {
        return $this->state(['tipo_endereco' => 'EMPRESARIAL']);
    }

    public function correspondencia(): static
    {
        return $this->state(['tipo_endereco' => 'CORRESPONDENCIA']);
    }
}
