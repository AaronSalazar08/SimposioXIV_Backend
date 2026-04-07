<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // LOGIN
    // =========================================================

    public function test_login_exitoso_devuelve_token_y_usuario(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'carnet' => $user->carnet,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_falla_con_password_incorrecto(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correcto')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'carnet' => $user->carnet,
            'password' => 'incorrecto',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_falla_con_carnet_incorrecto(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'carnet' => 'X99999',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_rechaza_email_no_ucr(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'usuario@gmail.com',
            'carnet' => 'C12345',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_rechaza_carnet_con_longitud_incorrecta(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'usuario@ucr.ac.cr',
            'carnet' => 'C123',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['carnet']);
    }

    public function test_login_rechaza_campos_requeridos_faltantes(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'carnet', 'password']);
    }

    // =========================================================
    // LOGOUT
    // =========================================================

    public function test_logout_revoca_el_token_actual(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('simposio-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Sesión cerrada correctamente.']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_logout_sin_autenticacion_devuelve_401(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertUnauthorized();
    }

    public function test_logout_solo_revoca_el_token_usado_no_los_demas(): void
    {
        $user = User::factory()->create();
        $tokenA = $user->createToken('token-a')->plainTextToken;
        $user->createToken('token-b');

        $this->withToken($tokenA)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    // =========================================================
    // ME
    // =========================================================

    public function test_me_devuelve_el_usuario_autenticado(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/me');

        $response->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_me_sin_autenticacion_devuelve_401(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertUnauthorized();
    }

    public function test_me_devuelve_los_datos_correctos_del_usuario(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/me');

        $response->assertOk()
            ->assertJsonFragment([
                'email' => $user->email,
                'carnet' => $user->carnet,
                'nombre' => $user->nombre,
            ]);
    }
}
