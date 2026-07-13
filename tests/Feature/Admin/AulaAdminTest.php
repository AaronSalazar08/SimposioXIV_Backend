<?php

namespace Tests\Feature\Admin;

use App\Models\Aula;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AulaAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_puede_listar_aulas(): void
    {
        Aula::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/admin/aulas');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_puede_crear_aula(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/aulas', [
            'numero' => '101',
            'edificio' => 'ECCI',
            'capacidad' => 30,
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['numero' => '101', 'edificio' => 'ECCI']);

        $this->assertDatabaseHas('aulas', ['numero' => '101', 'edificio' => 'ECCI']);
    }

    public function test_crear_aula_falla_con_capacidad_invalida(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/aulas', [
            'numero' => '101',
            'edificio' => 'ECCI',
            'capacidad' => 0,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['capacidad']);
    }

    public function test_admin_puede_actualizar_aula(): void
    {
        $aula = Aula::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/admin/aulas/{$aula->id}", [
            'capacidad' => 50,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('aulas', ['id' => $aula->id, 'capacidad' => 50]);
    }

    public function test_admin_puede_eliminar_aula(): void
    {
        $aula = Aula::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/aulas/{$aula->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('aulas', ['id' => $aula->id]);
    }

    public function test_no_puede_eliminar_aula_con_horarios_asociados(): void
    {
        $aula = Aula::factory()->create();
        Horario::factory()->create(['aula_id' => $aula->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/aulas/{$aula->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('aulas', ['id' => $aula->id]);
    }

    public function test_participante_no_puede_crear_aula(): void
    {
        $participante = User::factory()->create();

        $response = $this->actingAs($participante)->postJson('/api/admin/aulas', [
            'numero' => '101',
            'edificio' => 'ECCI',
            'capacidad' => 30,
        ]);

        $response->assertForbidden();
    }
}
