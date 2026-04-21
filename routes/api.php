<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\NoticiaController;

Route::prefix('v1')->group(function () {
    Route::get('materiales', [MaterialController::class, 'index']);
    Route::get('materiales/{material}', [MaterialController::class, 'show']);
    Route::get('noticias', [NoticiaController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('reservas', [\App\Http\Controllers\Api\ReservaController::class, 'index']);
        Route::post('reservas', [\App\Http\Controllers\Api\ReservaController::class, 'store']);
        Route::delete('reservas/{reserva}', [\App\Http\Controllers\Api\ReservaController::class, 'destroy']);
    });
});