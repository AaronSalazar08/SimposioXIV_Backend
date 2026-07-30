<?php

namespace Tests\Feature\Admin;

use App\Models\Aula;
use App\Models\Evento;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorarioAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_puede_crear_horario_sin_aula(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/horarios', [
            'numero_dia' => 2,
            'hora_inicio' => '06:00',
            'hora_fin' => '07:30',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.aula', null);

        $this->assertDatabaseHas('horarios', ['numero_dia' => 2, 'aula_id' => null]);
    }

    public function test_crear_horario_asigna_la_fecha_real_del_dia_del_simposio(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/horarios', [
            'numero_dia' => 2,
            'hora_inicio' => '16:00',
            'hora_fin' => '18:30',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('horarios', [
            'numero_dia' => 2,
            'hora_inicio' => '2026-08-06 16:00:00',
            'hora_fin' => '2026-08-06 18:30:00',
        ]);
    }

    public function test_horarios_del_mismo_dia_quedan_ordenados_cronologicamente_sin_importar_cuando_se_crearon(): void
    {
        // Simula horarios creados en momentos distintos (regresión del bug: la fecha
        // se completaba con "hoy" en vez de con la fecha real del simposio).
        $this->actingAs($this->admin)->postJson('/api/admin/horarios', [
            'numero_dia' => 2,
            'hora_inicio' => '16:00',
            'hora_fin' => '18:30',
        ]);

        $this->travel(3)->days();

        $this->actingAs($this->admin)->postJson('/api/admin/horarios', [
            'numero_dia' => 2,
            'hora_inicio' => '14:45',
            'hora_fin' => '15:30',
        ]);

        $horas = Horario::orderBy('hora_inicio')->pluck('hora_inicio')->map->format('H:i')->all();

        $this->assertSame(['14:45', '16:00'], $horas);
    }

    public function test_actualizar_numero_dia_recalcula_la_fecha_de_horas_no_enviadas(): void
    {
        $horario = Horario::factory()->create([
            'numero_dia' => 1,
            'hora_inicio' => '2026-08-05 16:00:00',
            'hora_fin' => '2026-08-05 18:30:00',
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/admin/horarios/{$horario->id}", [
            'numero_dia' => 3,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('horarios', [
            'id' => $horario->id,
            'numero_dia' => 3,
            'hora_inicio' => '2026-08-07 16:00:00',
            'hora_fin' => '2026-08-07 18:30:00',
        ]);
    }

    public function test_admin_puede_quitarle_el_aula_a_un_horario_existente(): void
    {
        $horario = Horario::factory()->create(['aula_id' => Aula::factory()->create()->id]);

        $response = $this->actingAs($this->admin)->putJson("/api/admin/horarios/{$horario->id}", [
            'aula_id' => null,
        ]);

        $response->assertOk()->assertJsonPath('data.aula', null);
        $this->assertDatabaseHas('horarios', ['id' => $horario->id, 'aula_id' => null]);
    }

    public function test_admin_puede_listar_horarios_con_y_sin_aula(): void
    {
        Horario::factory()->general()->create();
        Horario::factory()->create();

        $response = $this->actingAs($this->admin)->getJson('/api/admin/horarios');

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_admin_puede_eliminar_horario_sin_eventos_asociados(): void
    {
        $horario = Horario::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/horarios/{$horario->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('horarios', ['id' => $horario->id]);
    }

    public function test_no_puede_eliminar_horario_con_eventos_asociados(): void
    {
        $horario = Horario::factory()->create();
        Evento::factory()->create(['horario_id' => $horario->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/horarios/{$horario->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('horarios', ['id' => $horario->id]);
    }
}
