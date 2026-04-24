<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'e-LibeR API',
    version: '1.0.0',
    description: 'REST API pública y autenticada para el sistema de gestión bibliotecaria e-LibeR. Endpoints públicos: materiales y noticias. Endpoints protegidos: reservas (requieren Bearer token de Sanctum).'
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Token de Sanctum. Obtenerlo con POST /api/v1/login enviando usuario y password.'
)]
class ApiInfo {}
