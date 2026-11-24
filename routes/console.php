<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincronização diária do HUB com o TOTVS RM (procedures dbo.rm_sinc_*).
// Requer no servidor o cron: * * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('rm:sincronizar-procedures')
    ->dailyAt('02:00')
    ->timezone('America/Sao_Paulo')
    ->withoutOverlapping();
