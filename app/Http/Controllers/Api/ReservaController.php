<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ReservaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'Reservas endpoint - implementación futura']);
    }

    public function store(): JsonResponse
    {
        return response()->json(['message' => 'Reservas endpoint - implementación futura']);
    }

    public function destroy(int $reserva): JsonResponse
    {
        return response()->json(['message' => 'Reservas endpoint - implementación futura']);
    }
}