<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Aula;
use App\Models\Evento;
use App\Models\Horario;
use App\Models\Ponente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_sin_autenticacion_devuelve_401(): void
    {
        $this->getJson('/api/eventos')->assertUnauthorized();
    }

    public function test_index_devuelve_eventos_activos_con_horario_y_ponentes(): void
    {
        $user = User::factory()->create();
        $aula = Aula::factory()->create();
        $horario = Horario::factory()->create(['aula_id' => $aula->id, 'numero_dia' => 2]);
        $ponente = Ponente::factory()->create();
        $evento = Evento::factory()->create([
            'horario_id' => $horario->id,
            'esta_activo' => true,
        ]);
        $evento->ponentes()->sync([$ponente->id]);

        $response = $this->actingAs($user)->getJson('/api/eventos');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id', 'titulo', 'descripcion', 'tipo',
                        'capacidad', 'numero_inscritos', 'cupos_disponibles',
                        'horario' => ['id', 'numero_dia', 'hora_inicio', 'hora_fin', 'aula'],
                        'ponentes' => [['id', 'nombre', 'apellidos']],
                        'areas',
                    ],
                ],
            ]);
    }

    public function test_index_excluye_eventos_inactivos(): void
    {
        $user = User::factory()->create();
        Evento::factory()->create(['esta_activo' => false]);
        Evento::factory()->create(['esta_activo' => true]);

        $response = $this->actingAs($user)->getJson('/api/eventos');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_index_filtra_por_dia(): void
    {
        $user = User::factory()->create();
        $h1 = Horario::factory()->create(['numero_dia' => 1]);
        $h2 = Horario::factory()->create(['numero_dia' => 2]);
        Evento::factory()->create(['horario_id' => $h1->id]);
        Evento::factory()->create(['horario_id' => $h2->id]);

        $response = $this->actingAs($user)->getJson('/api/eventos?dia=2');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_index_filtra_por_solo_disponibles(): void
    {
        $user = User::factory()->create();
        Evento::factory()->create(['capacidad' => 10, 'numero_inscritos' => 10]);
        Evento::factory()->create(['capacidad' => 10, 'numero_inscritos' => 3]);

        $response = $this->actingAs($user)->getJson('/api/eventos?solo_disponibles=1');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_index_filtra_por_area(): void
    {
        $user = User::factory()->create();
        $area = Area::factory()->create();
        $eventoConArea = Evento::factory()->create();
        $eventoConArea->areas()->attach($area->id);
        Evento::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/eventos?area_id={$area->id}");

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_show_devuelve_detalle_del_evento(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/eventos/{$evento->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $evento->id)
            ->assertJsonPath('data.titulo', $evento->titulo);
    }

    public function test_show_evento_inexistente_devuelve_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/eventos/999999')->assertNotFound();
    }

    public function test_index_filtra_por_tipo(): void
    {
        $user = User::factory()->create();
        Evento::factory()->taller()->create();
        Evento::factory()->charla()->create();

        $this->actingAs($user)->getJson('/api/eventos?tipo=taller')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_index_rechaza_tipo_invalido(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/eventos?tipo=invalido')->assertUnprocessable();
    }

    public function test_index_rechaza_dia_invalido(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/eventos?dia=99')->assertUnprocessable();
    }
}
