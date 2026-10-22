<?php

namespace App\Providers;

use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // O Laravel envia datas como "Y-m-d H:i:s.v". Em colunas datetime, o SQL Server interpreta
        // esse formato conforme o DATEFORMAT da sessão; com o login em português (dmy), "2026-10-21"
        // vira ano-dia-mês e a gravação falha. Fixa ymd em toda conexão (inclusive reconexões).
        // unprepared() executa direto na sessão; em um statement preparado o SET seria desfeito ao fim do lote.
        Event::listen(function (ConnectionEstablished $event) {
            if ($event->connection->getDriverName() === 'sqlsrv') {
                $event->connection->unprepared('SET DATEFORMAT ymd');
            }
        });
    }
}
