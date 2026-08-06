<?php

namespace Tests\Feature\Admin;

use App\Models\Evento;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->staff()->create();
    }

    public function test_staff_puede_listar_eventos(): void
    {
        Evento::factory()->create();

        $response = $this->actingAs($this->staff)->getJson('/api/admin/eventos');

        $response->assertOk();
    }

    public function test_staff_puede_ver_un_evento(): void
    {
        $evento = Evento::factory()->create();

        $response = $this->actingAs($this->staff)->getJson("/api/admin/eventos/{$evento->id}");

        $response->assertOk();
    }

    public function test_staff_puede_listar_inscritos_de_evento(): void
    {
        $evento = Evento::factory()->create();
        Inscripcion::factory()->confirmada()->create(['evento_id' => $evento->id]);

        $response = $this->actingAs($this->staff)->getJson("/api/admin/eventos/{$evento->id}/inscritos");

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_staff_puede_marcar_asistencia(): void
    {
        $evento = Evento::factory()->create();
        $inscripcion = Inscripcion::factory()->confirmada()->create(['evento_id' => $evento->id]);

        $response = $this->actingAs($this->staff)
            ->putJson("/api/admin/eventos/{$evento->id}/inscritos/{$inscripcion->id}/asistencia", ['asistio' => true]);

        $response->assertOk()->assertJsonPath('data.asistio', true);
        $this->assertDatabaseHas('inscripciones', ['id' => $inscripcion->id, 'asistio' => true]);
    }

    public function test_staff_no_puede_crear_evento(): void
    {
        $horario = Horario::factory()->create();

        $response = $this->actingAs($this->staff)->postJson('/api/admin/eventos', [
            'titulo' => 'Evento no autorizado',
            'tipo' => 'charla',
            'capacidad' => 10,
            'horario_id' => $horario->id,
        ]);

        $response->assertForbidden();
    }

    public function test_staff_no_puede_actualizar_evento(): void
    {
        $evento = Evento::factory()->create();

        $response = $this->actingAs($this->staff)->putJson("/api/admin/eventos/{$evento->id}", ['titulo' => 'Nuevo título']);

        $response->assertForbidden();
    }

    public function test_staff_no_puede_eliminar_evento(): void
    {
        $evento = Evento::factory()->create();

        $response = $this->actingAs($this->staff)->deleteJson("/api/admin/eventos/{$evento->id}");

        $response->assertForbidden();
    }

    public function test_staff_no_puede_ver_usuarios(): void
    {
        $response = $this->actingAs($this->staff)->getJson('/api/admin/usuarios');

        $response->assertForbidden();
    }

    public function test_staff_no_puede_ver_horarios(): void
    {
        $response = $this->actingAs($this->staff)->getJson('/api/admin/horarios');

        $response->assertForbidden();
    }
}
