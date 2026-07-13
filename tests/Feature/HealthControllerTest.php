<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_devuelve_ok_con_base_de_datos_conectada(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
                'database' => ['connected' => true],
            ])
            ->assertJsonStructure([
                'status', 'app', 'env', 'php_version', 'laravel_version',
                'database' => ['connected', 'driver', 'error'],
                'timestamp',
            ]);
    }

    public function test_health_no_requiere_autenticacion(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk();
    }
}
