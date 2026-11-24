<?php

namespace App\Enums;

/**
 * Tipo do endereço da pessoa (inst_pessoa_enderecos.tipo_endereco).
 */
enum TipoEndereco: string
{
    case Pessoal = 'PESSOAL';
    case Empresarial = 'EMPRESARIAL';
    case Correspondencia = 'CORRESPONDENCIA';

    public function label(): string
    {
        return match ($this) {
            self::Pessoal => 'Pessoal',
            self::Empresarial => 'Empresarial',
            self::Correspondencia => 'Correspondência',
        };
    }

    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
