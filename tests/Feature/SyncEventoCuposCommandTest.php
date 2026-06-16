<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncEventoCuposCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_cupos_corrige_numero_inscritos_desincronizado(): void
    {
        $user = User::factory()->create();
        $evento = Evento::factory()->create(['capacidad' => 10, 'numero_inscritos' => 5]);

        Inscripcion::factory()->confirmada()->create([
            'user_id' => $user->id,
            'evento_id' => $evento->id,
        ]);

        $this->artisan('eventos:sync-cupos')->assertSuccessful();

        $this->assertSame(1, $evento->fresh()->numero_inscritos);
    }
}
