<?php

namespace Tests\Feature;

use App\Enums\EstadoInscripcion;
use App\Models\Evento;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InscripcionTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // STORE
    // =========================================================

    public function test_store_sin_autenticacion_devuelve_401(): void
    {
        $this->postJson('/api/inscripciones', ['evento_id' => 1])->assertUnauthorized();
    }

    public function test_store_inscribe_al_usuario_y_aumenta_numero_inscritos(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->create(['capacidad' => 10, 'numero_inscritos' => 0]);

        $response = $this->actingAs($user)->postJson('/api/inscripciones', [
            'evento_id' => $evento->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.estado', EstadoInscripcion::Confirmado->value)
            ->assertJsonPath('data.evento_id', $evento->id);

        $this->assertDatabaseHas('inscripciones', [
            'user_id' => $user->id,
            'evento_id' => $evento->id,
            'estado' => EstadoInscripcion::Confirmado->value,
        ]);

        $this->assertSame(1, $evento->fresh()->numero_inscritos);
    }

    public function test_store_rechaza_evento_inexistente(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/inscripciones', [
            'evento_id' => 999999,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['evento_id']);
    }

    public function test_store_rechaza_evento_inactivo(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->create(['esta_activo' => false]);

        $response = $this->actingAs($user)->postJson('/api/inscripciones', [
            'evento_id' => $evento->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['evento_id']);
    }

    public function test_store_rechaza_evento_sin_cupos(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->create(['capacidad' => 5, 'numero_inscritos' => 5]);

        $response = $this->actingAs($user)->postJson('/api/inscripciones', [
            'evento_id' => $evento->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['evento_id']);
    }

    public function test_store_rechaza_inscripcion_duplicada(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->create(['capacidad' => 10, 'numero_inscritos' => 1]);
        Inscripcion::factory()->confirmada()->create([
            'user_id' => $user->id,
            'evento_id' => $evento->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/inscripciones', [
            'evento_id' => $evento->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['evento_id']);
    }

    public function test_store_rechaza_solape_de_horario(): void
    {
        $user = User::factory()->create();
        $horario = Horario::factory()->create([
            'hora_inicio' => '2025-06-10 08:00:00',
            'hora_fin' => '2025-06-10 10:00:00',
        ]);
        $horarioSolapado = Horario::factory()->create([
            'hora_inicio' => '2025-06-10 09:00:00',
            'hora_fin' => '2025-06-10 11:00:00',
        ]);

        $eventoExistente = Evento::factory()->create(['horario_id' => $horario->id]);
        $eventoNuevo = Evento::factory()->create(['horario_id' => $horarioSolapado->id]);

        Inscripcion::factory()->confirmada()->create([
            'user_id' => $user->id,
            'evento_id' => $eventoExistente->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/inscripciones', [
            'evento_id' => $eventoNuevo->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['evento_id']);
    }

    public function test_store_permite_reinscribirse_si_anteriormente_canceló(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->create(['capacidad' => 10, 'numero_inscritos' => 0]);
        Inscripcion::factory()->cancelada()->create([
            'user_id' => $user->id,
            'evento_id' => $evento->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/inscripciones', [
            'evento_id' => $evento->id,
        ]);

        $response->assertCreated();
        $this->assertSame(1, $evento->fresh()->numero_inscritos);
    }

    // =========================================================
    // INDEX
    // =========================================================

    public function test_index_devuelve_solo_inscripciones_del_usuario(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();
        Inscripcion::factory()->confirmada()->count(2)->create(['user_id' => $user->id]);
        Inscripcion::factory()->confirmada()->create(['user_id' => $otro->id]);

        $response = $this->actingAs($user)->getJson('/api/inscripciones');

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_index_sin_autenticacion_devuelve_401(): void
    {
        $this->getJson('/api/inscripciones')->assertUnauthorized();
    }

    // =========================================================
    // DESTROY
    // =========================================================

    public function test_destroy_cancela_inscripcion_y_libera_cupo(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->create(['capacidad' => 10, 'numero_inscritos' => 1]);
        $inscripcion = Inscripcion::factory()->confirmada()->create([
            'user_id' => $user->id,
            'evento_id' => $evento->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/inscripciones/{$inscripcion->id}");

        $response->assertOk();
        $this->assertSame(EstadoInscripcion::Cancelado, $inscripcion->fresh()->estado);
        $this->assertSame(0, $evento->fresh()->numero_inscritos);
    }

    public function test_destroy_rechaza_inscripcion_de_otro_usuario(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();
        $inscripcion = Inscripcion::factory()->confirmada()->create(['user_id' => $otro->id]);

        $response = $this->actingAs($user)->deleteJson("/api/inscripciones/{$inscripcion->id}");

        $response->assertForbidden();
    }

    public function test_destroy_rechaza_inscripcion_ya_cancelada(): void
    {
        $user = User::factory()->create();
        $inscripcion = Inscripcion::factory()->cancelada()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/api/inscripciones/{$inscripcion->id}");

        $response->assertStatus(409);
    }

    public function test_index_filtra_por_estado_confirmado(): void
    {
        $user = User::factory()->create();
        Inscripcion::factory()->confirmada()->count(2)->create(['user_id' => $user->id]);
        Inscripcion::factory()->cancelada()->create(['user_id' => $user->id]);

        $this->actingAs($user)->getJson('/api/inscripciones?estado=confirmado')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_sin_filtro_incluye_canceladas(): void
    {
        $user = User::factory()->create();
        Inscripcion::factory()->confirmada()->create(['user_id' => $user->id]);
        Inscripcion::factory()->cancelada()->create(['user_id' => $user->id]);

        $this->actingAs($user)->getJson('/api/inscripciones')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
