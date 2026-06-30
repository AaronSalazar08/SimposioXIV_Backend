<?php

namespace Tests\Feature;

use App\Mail\CodigoOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordOtpTest extends TestCase
{
    use RefreshDatabase;

    // ── Enviar OTP ──────────────────────────────────────────────────────────

    public function test_usuario_autenticado_puede_solicitar_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/password/otp')
            ->assertOk()
            ->assertJsonFragment(['message' => 'Código enviado a tu correo electrónico.']);

        $this->assertTrue(Cache::has("otp:password:{$user->id}"));
        Mail::assertSent(CodigoOtpMail::class, fn ($m) => $m->hasTo($user->email));
    }

    public function test_usuario_no_autenticado_no_puede_solicitar_otp(): void
    {
        $this->postJson('/api/password/otp')->assertUnauthorized();
    }

    // ── Verificar OTP ────────────────────────────────────────────────────────

    public function test_verificar_codigo_correcto_marca_como_verificado(): void
    {
        $user = User::factory()->create();
        Cache::put("otp:password:{$user->id}", '123456', 120);

        $this->actingAs($user)
            ->postJson('/api/password/otp/verificar', ['codigo' => '123456'])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Código verificado correctamente.']);

        $this->assertFalse(Cache::has("otp:password:{$user->id}"));
        $this->assertTrue(Cache::has("otp:password:verified:{$user->id}"));
    }

    public function test_verificar_codigo_incorrecto_devuelve_error(): void
    {
        $user = User::factory()->create();
        Cache::put("otp:password:{$user->id}", '123456', 120);

        $this->actingAs($user)
            ->postJson('/api/password/otp/verificar', ['codigo' => '999999'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo']);
    }

    public function test_verificar_codigo_expirado_devuelve_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/password/otp/verificar', ['codigo' => '123456'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo']);
    }

    public function test_verificar_codigo_requiere_exactamente_6_digitos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/password/otp/verificar', ['codigo' => '12345'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo']);
    }

    // ── Cambiar contraseña ───────────────────────────────────────────────────

    public function test_cambiar_password_con_otp_verificado(): void
    {
        $user = User::factory()->create();
        Cache::put("otp:password:verified:{$user->id}", true, 300);

        $this->actingAs($user)
            ->putJson('/api/password/cambiar', [
                'password' => 'NuevaPass1!',
                'password_confirmation' => 'NuevaPass1!',
            ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Contraseña actualizada correctamente.']);

        $this->assertFalse(Cache::has("otp:password:verified:{$user->id}"));
    }

    public function test_cambiar_password_sin_otp_verificado_devuelve_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson('/api/password/cambiar', [
                'password' => 'NuevaPass1!',
                'password_confirmation' => 'NuevaPass1!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo']);
    }

    public function test_password_sin_mayuscula_es_invalida(): void
    {
        $user = User::factory()->create();
        Cache::put("otp:password:verified:{$user->id}", true, 300);

        $this->actingAs($user)
            ->putJson('/api/password/cambiar', [
                'password' => 'nuevapass1!',
                'password_confirmation' => 'nuevapass1!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_sin_numero_es_invalida(): void
    {
        $user = User::factory()->create();
        Cache::put("otp:password:verified:{$user->id}", true, 300);

        $this->actingAs($user)
            ->putJson('/api/password/cambiar', [
                'password' => 'NuevaPass!!',
                'password_confirmation' => 'NuevaPass!!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_sin_caracter_especial_es_invalida(): void
    {
        $user = User::factory()->create();
        Cache::put("otp:password:verified:{$user->id}", true, 300);

        $this->actingAs($user)
            ->putJson('/api/password/cambiar', [
                'password' => 'NuevaPass1',
                'password_confirmation' => 'NuevaPass1',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_menor_a_8_caracteres_es_invalida(): void
    {
        $user = User::factory()->create();
        Cache::put("otp:password:verified:{$user->id}", true, 300);

        $this->actingAs($user)
            ->putJson('/api/password/cambiar', [
                'password' => 'Np1!',
                'password_confirmation' => 'Np1!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_confirmacion_que_no_coincide_es_invalida(): void
    {
        $user = User::factory()->create();
        Cache::put("otp:password:verified:{$user->id}", true, 300);

        $this->actingAs($user)
            ->putJson('/api/password/cambiar', [
                'password' => 'NuevaPass1!',
                'password_confirmation' => 'OtraPass1!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
