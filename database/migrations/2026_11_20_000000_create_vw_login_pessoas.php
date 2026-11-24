<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cria a view dbo.vw_login_pessoas a partir de database/scripts/views/vw_login_pessoas.sql
 * e os índices que ela usa para buscar os vínculos de uma pessoa.
 *
 * Ao alterar a view, edite o .sql e rode o arquivo no SSMS (ou php artisan migrate:fresh).
 */
return new class extends Migration
{
    /** Tabela => [nome do índice => coluna]. */
    private const INDICES = [
        // Aluno: pessoa -> aluno -> enturmação.
        'edu_alunos' => [
            'ix_alunos_cod_pessoa' => 'cod_pessoa',
        ],
        'edu_enturmas' => [
            'ix_enturmas_cod_aluno' => 'cod_aluno',
        ],
        // Pai/mãe: familiar -> aluno.
        'edu_familias' => [
            'ix_familias_cod_pessoa' => 'cod_pessoa',
            'ix_familias_cod_pessoa_aluno' => 'cod_pessoa_aluno',
        ],
        // Responsáveis: pessoa -> responsável -> edu_responsaveis -> aluno.
        'edu_responsaveis_financeiros' => [
            'ix_responsaveis_financeiros_cod_pessoa_responsavel' => 'cod_pessoa_responsavel',
        ],
        'edu_responsaveis_pedagogico' => [
            'ix_responsaveis_pedagogico_cod_pessoa_responsavel' => 'cod_pessoa_responsavel',
        ],
        'edu_responsaveis' => [
            'ix_responsaveis_cod_responsavel_financeiro' => 'cod_responsavel_financeiro',
            'ix_responsaveis_cod_responsavel_pedagogico' => 'cod_responsavel_pedagogico',
        ],
    ];

    public function up(): void
    {
        foreach (self::INDICES as $tabela => $indices) {
            foreach ($indices as $nome => $coluna) {
                if (! Schema::hasIndex($tabela, $nome)) {
                    Schema::table($tabela, fn (Blueprint $table) => $table->index($coluna, $nome));
                }
            }
        }

        DB::unprepared(file_get_contents(database_path('scripts/views/vw_login_pessoas.sql')));
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS dbo.vw_login_pessoas');

        foreach (self::INDICES as $tabela => $indices) {
            foreach ($indices as $nome => $coluna) {
                if (Schema::hasIndex($tabela, $nome)) {
                    Schema::table($tabela, fn (Blueprint $table) => $table->dropIndex($nome));
                }
            }
        }
    }
};
