<?php

namespace Tests\Feature\Admin;

use App\Enums\EstadoInscripcion;
use App\Models\Evento;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Ponente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_puede_crear_evento_con_multiples_ponentes(): void
    {
        $horario = Horario::factory()->create();
        $ponentes = Ponente::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->postJson('/api/admin/eventos', [
            'titulo' => 'Panel de Ciberseguridad',
            'tipo' => 'charla',
            'capacidad' => 40,
            'horario_id' => $horario->id,
            'ponente_ids' => $ponentes->pluck('id')->all(),
        ]);

        $response->assertCreated()
            ->assertJsonCount(2, 'data.ponentes');

        $evento = Evento::where('titulo', 'Panel de Ciberseguridad')->firstOrFail();
        $this->assertDatabaseCount('evento_ponente', 2);
        $this->assertSame($ponentes->pluck('id')->sort()->values()->all(), $evento->ponentes()->pluck('ponentes.id')->sort()->values()->all());
    }

    public function test_admin_al_crear_evento_recibe_defaults_reales_de_capacidad_y_estado(): void
    {
        $horario = Horario::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/admin/eventos', [
            'titulo' => 'Evento sin overrides',
            'tipo' => 'charla',
            'capacidad' => 40,
            'horario_id' => $horario->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.numero_inscritos', 0)
            ->assertJsonPath('data.esta_activo', true);
    }

    public function test_admin_puede_reemplazar_ponentes_de_evento_al_actualizar(): void
    {
        $evento = Evento::factory()->create();
        $ponenteOriginal = $evento->ponentes()->first();
        $nuevoPonente = Ponente::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/admin/eventos/{$evento->id}", [
            'ponente_ids' => [$nuevoPonente->id],
        ]);

        $response->assertOk();
        $evento->refresh();
        $this->assertEqualsCanonicalizing([$nuevoPonente->id], $evento->ponentes()->pluck('ponentes.id')->all());
        $this->assertDatabaseMissing('evento_ponente', ['evento_id' => $evento->id, 'ponente_id' => $ponenteOriginal->id]);
    }

    public function test_admin_puede_listar_inscritos_confirmados_de_evento(): void
    {
        $evento = Evento::factory()->create();
        $inscrito = User::factory()->create(['nombre' => 'María Fernández', 'carnet' => 'C12345']);
        $cancelado = User::factory()->create(['nombre' => 'Usuario Cancelado']);

        Inscripcion::factory()->confirmada()->create(['user_id' => $inscrito->id, 'evento_id' => $evento->id]);
        Inscripcion::factory()->create(['user_id' => $cancelado->id, 'evento_id' => $evento->id, 'estado' => EstadoInscripcion::Cancelado]);

        $response = $this->actingAs($this->admin)->getJson("/api/admin/eventos/{$evento->id}/inscritos");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['nombre' => 'María Fernández', 'email' => $inscrito->email, 'carnet' => 'C12345'])
            ->assertJsonMissing(['nombre' => 'Usuario Cancelado'])
            ->assertJsonPath('data.0.asistio', false);
    }

    public function test_participante_no_puede_ver_inscritos_de_evento(): void
    {
        $evento = Evento::factory()->create();
        $participante = User::factory()->create();

        $response = $this->actingAs($participante)->getJson("/api/admin/eventos/{$evento->id}/inscritos");

        $response->assertForbidden();
    }

    public function test_admin_puede_marcar_asistencia_de_un_inscrito(): void
    {
        $evento = Evento::factory()->create();
        $inscripcion = Inscripcion::factory()->confirmada()->create(['evento_id' => $evento->id]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/eventos/{$evento->id}/inscritos/{$inscripcion->id}/asistencia", ['asistio' => true]);

        $response->assertOk()
            ->assertJsonPath('data.asistio', true);
        $this->assertDatabaseHas('inscripciones', ['id' => $inscripcion->id, 'asistio' => true]);
    }

    public function test_admin_puede_desmarcar_asistencia_de_un_inscrito(): void
    {
        $evento = Evento::factory()->create();
        $inscripcion = Inscripcion::factory()->confirmada()->create(['evento_id' => $evento->id, 'asistio' => true]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/eventos/{$evento->id}/inscritos/{$inscripcion->id}/asistencia", ['asistio' => false]);

        $response->assertOk()
            ->assertJsonPath('data.asistio', false);
        $this->assertDatabaseHas('inscripciones', ['id' => $inscripcion->id, 'asistio' => false]);
    }

    public function test_participante_no_puede_marcar_asistencia(): void
    {
        $evento = Evento::factory()->create();
        $inscripcion = Inscripcion::factory()->confirmada()->create(['evento_id' => $evento->id]);
        $participante = User::factory()->create();

        $response = $this->actingAs($participante)
            ->putJson("/api/admin/eventos/{$evento->id}/inscritos/{$inscripcion->id}/asistencia", ['asistio' => true]);

        $response->assertForbidden();
        $this->assertDatabaseHas('inscripciones', ['id' => $inscripcion->id, 'asistio' => false]);
    }

    public function test_no_puede_marcar_asistencia_de_inscripcion_de_otro_evento(): void
    {
        $evento = Evento::factory()->create();
        $otroEvento = Evento::factory()->create();
        $inscripcion = Inscripcion::factory()->confirmada()->create(['evento_id' => $otroEvento->id]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/eventos/{$evento->id}/inscritos/{$inscripcion->id}/asistencia", ['asistio' => true]);

        $response->assertNotFound();
    }

    public function test_admin_puede_eliminar_evento_sin_inscripciones_confirmadas(): void
    {
        $evento = Evento::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/eventos/{$evento->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('eventos', ['id' => $evento->id]);
    }

    public function test_no_puede_eliminar_evento_con_inscripciones_confirmadas(): void
    {
        $evento = Evento::factory()->create();
        Inscripcion::factory()->confirmada()->create(['evento_id' => $evento->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/eventos/{$evento->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('eventos', ['id' => $evento->id]);
    }

    public function test_puede_eliminar_evento_con_solo_inscripciones_canceladas(): void
    {
        $evento = Evento::factory()->create();
        Inscripcion::factory()->cancelada()->create(['evento_id' => $evento->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/eventos/{$evento->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('eventos', ['id' => $evento->id]);
    }
}
