<?php

namespace Tests\Unit;

use App\Models\Horario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_se_solapa_con_detecta_horarios_superpuestos(): void
    {
        $horarioA = Horario::factory()->create([
            'hora_inicio' => '2025-06-10 08:00:00',
            'hora_fin' => '2025-06-10 10:00:00',
        ]);

        $horarioB = Horario::factory()->create([
            'hora_inicio' => '2025-06-10 09:00:00',
            'hora_fin' => '2025-06-10 11:00:00',
        ]);

        $this->assertTrue($horarioA->seSolapaCon($horarioB));
    }

    public function test_se_solapa_con_rechaza_horarios_consecutivos(): void
    {
        $horarioA = Horario::factory()->create([
            'hora_inicio' => '2025-06-10 08:00:00',
            'hora_fin' => '2025-06-10 10:00:00',
        ]);

        $horarioB = Horario::factory()->create([
            'hora_inicio' => '2025-06-10 10:00:00',
            'hora_fin' => '2025-06-10 12:00:00',
        ]);

        $this->assertFalse($horarioA->seSolapaCon($horarioB));
    }
}
