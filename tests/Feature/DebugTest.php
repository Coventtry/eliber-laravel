<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TestHelpers;
use Tests\TestCase;

class DebugTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    public function test_acting_as_web(): void
    {
        $user = $this->createBibliotecario();
        $this->actingAs($user); // default web guard

        $response = $this->postJson('/api/v1/socios', [
            'nombre' => 'Test',
            'apellido' => 'Test',
            'email' => 'test@test.com',
            'telefono' => '2454111111',
            'anio' => 4,
            'division' => 3,
        ]);

        // If auth:sanctum fails, this will be 401. If authorize fails, 403.
        // If success, 201.
        echo 'Status: ' . $response->status() . PHP_EOL;
        echo 'Body: ' . $response->getContent() . PHP_EOL;
    }
}
