<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoUsuario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    // =========================================================
    // LISTAR
    // =========================================================

    public function test_admin_puede_listar_usuarios(): void
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/admin/usuarios');

        $response->assertOk()
            ->assertJsonCount(4, 'data');
    }

    // =========================================================
    // CREAR
    // =========================================================

    public function test_admin_puede_crear_usuario_con_password_generada(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/usuarios', [
            'nombre' => 'Juan Pérez',
            'email' => 'juan@ucr.ac.cr',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data', 'password_generada'])
            ->assertJsonFragment(['email' => 'juan@ucr.ac.cr']);

        $this->assertDatabaseHas('users', ['email' => 'juan@ucr.ac.cr']);
    }

    public function test_admin_puede_crear_usuario_con_password_manual(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/usuarios', [
            'nombre' => 'Ana Mora',
            'email' => 'ana@ucr.ac.cr',
            'password' => 'MiPassword1',
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['password_generada' => 'MiPassword1']);
    }

    public function test_crear_usuario_falla_con_email_duplicado(): void
    {
        User::factory()->create(['email' => 'duplicado@ucr.ac.cr']);

        $response = $this->actingAs($this->admin)->postJson('/api/admin/usuarios', [
            'nombre' => 'Otro',
            'email' => 'duplicado@ucr.ac.cr',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_puede_generar_password_aleatoria(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/admin/passwords/generar');

        $response->assertOk()
            ->assertJsonStructure(['password']);

        $password = $response->json('password');
        $this->assertGreaterThanOrEqual(12, strlen($password));
    }

    // =========================================================
    // ACTUALIZAR
    // =========================================================

    public function test_admin_puede_actualizar_usuario(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/admin/usuarios/{$user->id}", [
            'nombre' => 'Nombre Actualizado',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['nombre' => 'Nombre Actualizado']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'nombre' => 'Nombre Actualizado']);
    }

    public function test_admin_puede_cambiar_tipo_usuario(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/admin/usuarios/{$user->id}", [
            'tipo_usuario' => TipoUsuario::Admin->value,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'tipo_usuario' => 'admin']);
    }

    // =========================================================
    // ELIMINAR
    // =========================================================

    public function test_admin_puede_eliminar_usuario(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/usuarios/{$user->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_eliminar_usuario_inexistente_devuelve_404(): void
    {
        $response = $this->actingAs($this->admin)->deleteJson('/api/admin/usuarios/9999');

        $response->assertNotFound();
    }
}
