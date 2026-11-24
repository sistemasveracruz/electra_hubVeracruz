<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cria a view dbo.vw_alunos a partir de database/scripts/views/vw_alunos.sql.
 *
 * Ao alterar a view, edite o .sql e rode o arquivo no SSMS (ou php artisan migrate:fresh).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(file_get_contents(database_path('scripts/views/vw_alunos.sql')));
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS dbo.vw_alunos');
    }
};
