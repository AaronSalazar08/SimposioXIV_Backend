<?php

namespace Tests\Feature;

use App\Models\Horario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixHorarioFechasCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_corrige_la_fecha_de_horarios_con_fecha_incorrecta(): void
    {
        $horario = Horario::factory()->create([
            'numero_dia' => 2,
            'hora_inicio' => '2026-07-01 16:00:00',
            'hora_fin' => '2026-07-01 18:30:00',
        ]);

        $this->artisan('horarios:corregir-fechas')->assertSuccessful();

        $horario->refresh();
        $this->assertSame('2026-08-06 16:00:00', $horario->hora_inicio->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-06 18:30:00', $horario->hora_fin->format('Y-m-d H:i:s'));
    }

    public function test_dry_run_no_guarda_cambios(): void
    {
        $horario = Horario::factory()->create([
            'numero_dia' => 2,
            'hora_inicio' => '2026-07-01 16:00:00',
            'hora_fin' => '2026-07-01 18:30:00',
        ]);

        $this->artisan('horarios:corregir-fechas', ['--dry-run' => true])->assertSuccessful();

        $horario->refresh();
        $this->assertSame('2026-07-01 16:00:00', $horario->hora_inicio->format('Y-m-d H:i:s'));
    }

    public function test_no_toca_horarios_que_ya_tienen_la_fecha_correcta(): void
    {
        $horario = Horario::factory()->create([
            'numero_dia' => 1,
            'hora_inicio' => '2026-08-05 15:00:00',
            'hora_fin' => '2026-08-05 17:30:00',
        ]);

        $this->artisan('horarios:corregir-fechas')
            ->expectsOutputToContain('0 horario(s) corregido(s).')
            ->assertSuccessful();

        $horario->refresh();
        $this->assertSame('2026-08-05 15:00:00', $horario->hora_inicio->format('Y-m-d H:i:s'));
    }
}
