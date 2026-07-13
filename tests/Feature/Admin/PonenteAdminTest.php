<?php

namespace Tests\Feature\Admin;

use App\Models\Evento;
use App\Models\Ponente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PonenteAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_puede_eliminar_ponente_sin_eventos_asociados(): void
    {
        $ponente = Ponente::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/ponentes/{$ponente->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('ponentes', ['id' => $ponente->id]);
    }

    public function test_no_puede_eliminar_ponente_asignado_a_un_evento(): void
    {
        // Evento::factory() adjunta un ponente automáticamente (ver EventoFactory::configure).
        $evento = Evento::factory()->create();
        $ponente = $evento->ponentes()->firstOrFail();

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/ponentes/{$ponente->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('ponentes', ['id' => $ponente->id]);
    }
}
