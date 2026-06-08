<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_participante_no_puede_acceder_a_rutas_admin(): void
    {
        $participante = User::factory()->create();

        $response = $this->actingAs($participante)->getJson('/api/admin/usuarios');

        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado_no_puede_acceder_a_rutas_admin(): void
    {
        $response = $this->getJson('/api/admin/usuarios');

        $response->assertUnauthorized();
    }

    public function test_admin_puede_acceder_a_rutas_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/usuarios');

        $response->assertOk();
    }

    public function test_login_devuelve_tipo_usuario_en_respuesta(): void
    {
        $admin = User::factory()->admin()->create(['password' => bcrypt('secret123')]);

        $response = $this->postJson('/api/login', [
            'identifier' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['tipo_usuario' => 'admin']);
    }
}
