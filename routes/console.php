<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('reservas:expirar')->everyMinute();
Schedule::command('prestamos:marcar-atrasados')->dailyAt('00:00');
Schedule::command('db:respaldo --keep=7')->dailyAt('03:00');
