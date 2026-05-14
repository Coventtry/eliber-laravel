<?php

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reservas:expirar')->hourly()->withoutOverlapping();
Schedule::command('prestamos:marcar-atrasados')->dailyAt('01:00')->withoutOverlapping();

Schedule::command('db:respaldo --keep=7')->dailyAt('03:00')->withoutOverlapping();
