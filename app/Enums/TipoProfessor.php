<?php

namespace App\Enums;

/**
 * Papel do professor na turma/disciplina. No HUB é gravado por extenso (TITULAR, ORIENTADOR...);
 * no TOTVS RM vem como código de uma letra em SPROFESSORTURMA.TIPOPROF (ver codigoRm()/doRm()).
 */
enum TipoProfessor: string
{
    case Adjunto = 'ADJUNTO';
    case Assistente = 'ASSISTENTE';
    case Auxiliar = 'AUXILIAR';
    case Coordenador = 'COORDENADOR';
    case Colaborador = 'COLABORADOR';
    case Conteudista = 'CONTEUDISTA';
    case DiretorDeTurma = 'DIRETOR_DE_TURMA';
    case MediadorPedagogico = 'MEDIADOR_PEDAGOGICO';
    case Monitor = 'MONITOR';
    case NaoDocente = 'NAO_DOCENTE';
    case Orientador = 'ORIENTADOR';
    case Outros = 'OUTROS';
    case Substituto = 'SUBSTITUTO';
    case Titular = 'TITULAR';
    case Tutor = 'TUTOR';

    public function label(): string
    {
        return match ($this) {
            self::Adjunto => 'Adjunto',
            self::Assistente => 'Assistente',
            self::Auxiliar => 'Auxiliar',
            self::Coordenador => 'Coordenador',
            self::Colaborador => 'Colaborador',
            self::Conteudista => 'Conteudista',
            self::DiretorDeTurma => 'Diretor de Turma',
            self::MediadorPedagogico => 'Mediador pedagógico',
            self::Monitor => 'Monitor',
            self::NaoDocente => 'Não docente',
            self::Orientador => 'Orientador',
            self::Outros => 'Outros',
            self::Substituto => 'Substituto',
            self::Titular => 'Titular',
            self::Tutor => 'Tutor',
        };
    }

    /** Código de uma letra usado no RM (SPROFESSORTURMA.TIPOPROF). */
    public function codigoRm(): string
    {
        return match ($this) {
            self::Adjunto => 'J',
            self::Assistente => 'I',
            self::Auxiliar => 'X',
            self::Coordenador => 'D',
            self::Colaborador => 'C',
            self::Conteudista => 'P',
            self::DiretorDeTurma => 'E',
            self::MediadorPedagogico => 'Q',
            self::Monitor => 'M',
            self::NaoDocente => 'N',
            self::Orientador => 'R',
            self::Outros => 'O',
            self::Substituto => 'S',
            self::Titular => 'T',
            self::Tutor => 'U',
        };
    }

    /** Converte o código do RM; null quando vazio ou desconhecido. */
    public static function doRm(?string $codigo): ?self
    {
        $codigo = strtoupper(trim((string) $codigo));

        foreach (self::cases() as $caso) {
            if ($caso->codigoRm() === $codigo) {
                return $caso;
            }
        }

        return null;
    }

    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
