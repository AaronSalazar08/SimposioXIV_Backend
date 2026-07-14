<?php

namespace Tests\Feature;

use App\Mail\BienvenidaSimposio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BienvenidaSimposioMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_boton_del_correo_usa_la_url_configurada_del_frontend(): void
    {
        config(['services.frontend.url' => 'https://sieguanacaste.com']);

        $usuario = User::factory()->create();
        $html = (new BienvenidaSimposio($usuario, 'ClaveTemporal123!'))->render();

        $this->assertStringContainsString('https://sieguanacaste.com', $html);
        $this->assertStringNotContainsString('localhost:5173', $html);
    }

    public function test_el_boton_usa_localhost_solo_si_no_hay_frontend_url_configurada(): void
    {
        config(['services.frontend.url' => 'http://localhost:5173']);

        $usuario = User::factory()->create();
        $html = (new BienvenidaSimposio($usuario, 'ClaveTemporal123!'))->render();

        $this->assertStringContainsString('http://localhost:5173', $html);
    }
}
