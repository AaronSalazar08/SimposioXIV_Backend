<?php

namespace Tests\Feature\Admin;

use App\Models\Area;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_puede_eliminar_area_sin_eventos_asociados(): void
    {
        $area = Area::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/areas/{$area->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('areas', ['id' => $area->id]);
    }

    public function test_no_puede_eliminar_area_asignada_a_un_evento(): void
    {
        $area = Area::factory()->create();
        $evento = Evento::factory()->create();
        $evento->areas()->attach($area->id);

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/areas/{$area->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('areas', ['id' => $area->id]);
    }
}
