<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiTesterPageTest extends TestCase
{
    public function test_la_pagina_carga_cuando_esta_habilitada(): void
    {
        config(['services.api_tester.enabled' => true]);

        $response = $this->get('/api-tester');

        $response->assertOk()
            ->assertSee('API Tester');
    }

    public function test_la_pagina_devuelve_404_cuando_esta_deshabilitada(): void
    {
        config(['services.api_tester.enabled' => false]);

        $response = $this->get('/api-tester');

        $response->assertNotFound();
    }
}
