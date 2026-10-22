<?php

namespace Database\Seeders\Support;

use Faker\Generator;
use Illuminate\Support\Str;

/**
 * Geradores de dados fictícios para o ambiente de desenvolvimento.
 *
 * CPF e CNPJ são derivados de um índice (com dígitos verificadores válidos),
 * o que dá identidade estável às pessoas entre execuções do seed.
 */
final class DevData
{
    private const BAIRROS = [
        'Pinheiros', 'Vila Mariana', 'Butantã', 'Perdizes', 'Santo Amaro',
        'Moema', 'Lapa', 'Vila Madalena', 'Campo Belo', 'Morumbi',
    ];

    private const TIPOS_LOGRADOURO = ['Rua', 'Avenida', 'Alameda', 'Travessa'];

    public static function faker(): Generator
    {
        return fake('pt_BR');
    }

    /**
     * CPF (11 dígitos, sem máscara) determinístico para o índice informado.
     */
    public static function cpf(int $indice): string
    {
        $base = sprintf('%09d', 100000000 + $indice);
        $d1 = self::digitoVerificador($base, range(10, 2));
        $d2 = self::digitoVerificador($base.$d1, range(11, 2));

        return $base.$d1.$d2;
    }

    /**
     * CNPJ (14 dígitos, sem máscara) determinístico para o índice informado.
     */
    public static function cnpj(int $indice): string
    {
        $base = sprintf('%08d', 10000000 + $indice).'0001';
        $d1 = self::digitoVerificador($base, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        $d2 = self::digitoVerificador($base.$d1, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return $base.$d1.$d2;
    }

    /**
     * Data de nascimento (Y-m-d) para quem tem a idade informada em 31/03 do ano de referência
     * (data de corte usada para enturmação por idade).
     */
    public static function dataNascimento(int $idade, int $anoReferencia = Catalogo::ANO_LETIVO_ATUAL): string
    {
        $inicio = sprintf('%d-04-01', $anoReferencia - $idade - 1);
        $fim = sprintf('%d-03-31', $anoReferencia - $idade);

        return self::faker()->dateTimeBetween($inicio, $fim)->format('Y-m-d');
    }

    /**
     * Endereço fictício em São Paulo/SP, com os campos de inst_pessoa_enderecos (exceto pessoa e tipo).
     *
     * @return array<string, string|null>
     */
    public static function endereco(): array
    {
        $faker = self::faker();

        return [
            'cep' => '0'.$faker->numerify('#######'),
            'logradouro' => $faker->randomElement(self::TIPOS_LOGRADOURO).' '.$faker->firstName().' '.$faker->lastName(),
            'numero' => (string) $faker->numberBetween(1, 3500),
            'complemento' => $faker->optional(0.35)->randomElement([
                'Apto '.$faker->numberBetween(11, 184), 'Casa 2', 'Bloco B', 'Fundos',
            ]),
            'bairro' => $faker->randomElement(self::BAIRROS),
            'cidade' => 'São Paulo',
            'uf' => 'SP',
        ];
    }

    /**
     * E-mail fictício derivado do nome, em domínio reservado para exemplos.
     */
    public static function email(string $nome, string $dominio = 'example.com'): string
    {
        return Str::slug($nome, '.').'@'.$dominio;
    }

    /**
     * @param  list<int>  $pesos
     */
    private static function digitoVerificador(string $base, array $pesos): int
    {
        $soma = 0;

        foreach (str_split($base) as $i => $digito) {
            $soma += (int) $digito * $pesos[$i];
        }

        $resto = $soma % 11;

        return $resto < 2 ? 0 : 11 - $resto;
    }
}
