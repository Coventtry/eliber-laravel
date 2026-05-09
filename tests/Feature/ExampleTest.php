<?php

namespace Tests\Feature;

use App\Models\Institucion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        Institucion::create([
            'nombre' => 'Default',
            'slug' => 'default',
            'estado' => 'activa',
        ]);

        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
