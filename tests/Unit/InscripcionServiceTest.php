<?php

namespace Tests\Unit;

use App\Exceptions\Inscripcion\CupoAgotadoException;
use App\Exceptions\Inscripcion\EventoInactivoException;
use App\Models\Evento;
use App\Models\User;
use App\Services\InscripcionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InscripcionServiceTest extends TestCase
{
    use RefreshDatabase;

    private InscripcionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InscripcionService;
    }

    public function test_inscribir_lanza_excepcion_si_evento_inactivo(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->create(['esta_activo' => false]);

        $this->expectException(EventoInactivoException::class);

        $this->service->inscribir($user->id, $evento->id);
    }

    public function test_inscribir_lanza_excepcion_si_no_hay_cupos(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->sinCupos()->create(['capacidad' => 5]);

        $this->expectException(CupoAgotadoException::class);

        $this->service->inscribir($user->id, $evento->id);
    }
}
