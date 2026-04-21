<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\NoticiaController;
use App\Http\Controllers\Api\ReservaController;

Route::prefix('v1')->group(function () {
    Route::get('materiales', [MaterialController::class, 'index']);
    Route::get('materiales/{material}', [MaterialController::class, 'show']);
    Route::get('noticias', [NoticiaController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('reservas', [ReservaController::class, 'index']);
        Route::post('reservas', [ReservaController::class, 'store']);
        Route::delete('reservas/{reserva}', [ReservaController::class, 'destroy']);
        Route::patch('reservas/{reserva}/aprobar', [ReservaController::class, 'aprobar']);
    });
});