<?php

namespace Database\Seeders;

use App\Models\Aula;
use App\Models\Horario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HorarioSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $auditorio = Aula::where('edificio', 'Auditorio')->first();
        $hotel = Aula::where('edificio', 'Hotel Las Espuelas')->first();
        $salonMultiusos = Aula::where('edificio', 'UCR - Salón Multiusos')->first();
        $gimnasio = Aula::where('edificio', 'Gimnasio UCR')->first();
        $aulas = Aula::whereNotIn('edificio', [
            'Auditorio', 'Hotel Las Espuelas', 'UCR - Salón Multiusos', 'Gimnasio UCR',
        ])->get()->keyBy('numero');

        $horarios = [
            // ── Día 1 · 05 Ago 2026 ─────────────────────────────────────
            // 15:00-17:30  Asignación de habitaciones + Traslado (slot compartido)
            [
                'aula_id' => $hotel?->id,
                'numero_dia' => 1,
                'hora_inicio' => '2026-08-05 15:00:00',
                'hora_fin' => '2026-08-05 17:30:00',
            ],
            // 17:30-18:30  Cena
            [
                'aula_id' => $salonMultiusos?->id,
                'numero_dia' => 1,
                'hora_inicio' => '2026-08-05 17:30:00',
                'hora_fin' => '2026-08-05 18:30:00',
            ],
            // 19:00-21:00  Actividad Deportiva
            [
                'aula_id' => $gimnasio?->id,
                'numero_dia' => 1,
                'hora_inicio' => '2026-08-05 19:00:00',
                'hora_fin' => '2026-08-05 21:00:00',
            ],

            // ── Día 2 · 06 Ago 2026 ─────────────────────────────────────
            // franja 08:00-10:00
            [
                'aula_id' => $aulas->get(101)?->id,
                'numero_dia' => 2,
                'hora_inicio' => '2026-08-06 08:00:00',
                'hora_fin' => '2026-08-06 10:00:00',
            ],
            [
                'aula_id' => $aulas->get(105)?->id,
                'numero_dia' => 2,
                'hora_inicio' => '2026-08-06 08:00:00',
                'hora_fin' => '2026-08-06 10:00:00',
            ],
            // franja 10:30-12:00
            [
                'aula_id' => $aulas->get(201)?->id,
                'numero_dia' => 2,
                'hora_inicio' => '2026-08-06 10:30:00',
                'hora_fin' => '2026-08-06 12:00:00',
            ],
            [
                'aula_id' => $aulas->get(202)?->id,
                'numero_dia' => 2,
                'hora_inicio' => '2026-08-06 10:30:00',
                'hora_fin' => '2026-08-06 12:00:00',
            ],

            // ── Día 3 · 07 Ago 2026 ─────────────────────────────────────
            // franja 08:00-10:00
            [
                'aula_id' => $aulas->get(101)?->id,
                'numero_dia' => 3,
                'hora_inicio' => '2026-08-07 08:00:00',
                'hora_fin' => '2026-08-07 10:00:00',
            ],
            [
                'aula_id' => $aulas->get(105)?->id,
                'numero_dia' => 3,
                'hora_inicio' => '2026-08-07 08:00:00',
                'hora_fin' => '2026-08-07 10:00:00',
            ],
            // franja 10:30-12:00
            [
                'aula_id' => $aulas->get(201)?->id,
                'numero_dia' => 3,
                'hora_inicio' => '2026-08-07 10:30:00',
                'hora_fin' => '2026-08-07 12:00:00',
            ],
            // Clausura
            [
                'aula_id' => $auditorio?->id,
                'numero_dia' => 3,
                'hora_inicio' => '2026-08-07 14:00:00',
                'hora_fin' => '2026-08-07 16:00:00',
            ],
        ];

        foreach ($horarios as $horario) {
            Horario::create($horario);
        }
    }
}
