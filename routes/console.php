<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Limpieza diaria de notificaciones (leídas antiguas + auto-marcado de vencidas)
Schedule::command('notifications:clean')->dailyAt('04:00');
