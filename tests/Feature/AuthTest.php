<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // LOGIN CON EMAIL
    // =========================================================

    public function test_login_exitoso_con_email_devuelve_token_y_usuario(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $response = $this->postJson('/api/login', [
            'identifier' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_falla_con_password_incorrecto_usando_email(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correcto')]);

        $response = $this->postJson('/api/login', [
            'identifier' => $user->email,
            'password' => 'incorrecto',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['identifier']);
    }

    public function test_login_rechaza_email_no_ucr(): void
    {
        $response = $this->postJson('/api/login', [
            'identifier' => 'usuario@gmail.com',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['identifier']);
    }

    public function test_login_rechaza_email_de_otro_dominio_ucr(): void
    {
        $response = $this->postJson('/api/login', [
            'identifier' => 'usuario@ecci.ucr.ac.cr',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['identifier']);
    }

    // =========================================================
    // LOGIN CON CARNET
    // =========================================================

    public function test_login_exitoso_con_carnet_devuelve_token_y_usuario(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $response = $this->postJson('/api/login', [
            'identifier' => $user->carnet,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_falla_con_password_incorrecto_usando_carnet(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correcto')]);

        $response = $this->postJson('/api/login', [
            'identifier' => $user->carnet,
            'password' => 'incorrecto',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['identifier']);
    }

    public function test_login_rechaza_carnet_con_menos_de_6_caracteres(): void
    {
        $response = $this->postJson('/api/login', [
            'identifier' => 'C123',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['identifier']);
    }

    public function test_login_rechaza_carnet_con_mas_de_6_caracteres(): void
    {
        $response = $this->postJson('/api/login', [
            'identifier' => 'C123456',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['identifier']);
    }

    public function test_login_rechaza_carnet_con_caracteres_especiales(): void
    {
        $response = $this->postJson('/api/login', [
            'identifier' => 'C1234!',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['identifier']);
    }

    public function test_login_falla_cuando_carnet_no_existe(): void
    {
        $response = $this->postJson('/api/login', [
            'identifier' => 'X99999',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['identifier']);
    }

    // =========================================================
    // LOGIN — CAMPOS REQUERIDOS
    // =========================================================

    public function test_login_rechaza_campos_requeridos_faltantes(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['identifier', 'password']);
    }

    public function test_login_rechaza_sin_identifier(): void
    {
        $response = $this->postJson('/api/login', [
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['identifier']);
    }

    public function test_login_rechaza_sin_password(): void
    {
        $response = $this->postJson('/api/login', [
            'identifier' => 'C12345',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
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

    public function test_login_limita_intentos_repetidos(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'identifier' => $user->email,
                'password' => 'incorrecto',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/login', [
            'identifier' => $user->email,
            'password' => 'incorrecto',
        ])->assertStatus(429);
    }
}
