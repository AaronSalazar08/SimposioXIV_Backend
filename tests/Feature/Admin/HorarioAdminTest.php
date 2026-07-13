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
