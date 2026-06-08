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
        $aulas = Aula::where('edificio', '!=', 'Auditorio')->get()->keyBy('numero');

        $horarios = [
            // Día 1
            [
                'aula_id' => $auditorio?->id,
                'numero_dia' => 1,
                'hora_inicio' => '2025-06-09 08:00:00',
                'hora_fin' => '2025-06-09 10:00:00',
            ],
            // Día 2 — franja 08:00-10:00
            [
                'aula_id' => $aulas->get(101)?->id,
                'numero_dia' => 2,
                'hora_inicio' => '2025-06-10 08:00:00',
                'hora_fin' => '2025-06-10 10:00:00',
            ],
            [
                'aula_id' => $aulas->get(105)?->id,
                'numero_dia' => 2,
                'hora_inicio' => '2025-06-10 08:00:00',
                'hora_fin' => '2025-06-10 10:00:00',
            ],
            // Día 2 — franja 10:30-12:00
            [
                'aula_id' => $aulas->get(201)?->id,
                'numero_dia' => 2,
                'hora_inicio' => '2025-06-10 10:30:00',
                'hora_fin' => '2025-06-10 12:00:00',
            ],
            [
                'aula_id' => $aulas->get(202)?->id,
                'numero_dia' => 2,
                'hora_inicio' => '2025-06-10 10:30:00',
                'hora_fin' => '2025-06-10 12:00:00',
            ],
            // Día 3 — franja 08:00-10:00
            [
                'aula_id' => $aulas->get(101)?->id,
                'numero_dia' => 3,
                'hora_inicio' => '2025-06-11 08:00:00',
                'hora_fin' => '2025-06-11 10:00:00',
            ],
            [
                'aula_id' => $aulas->get(105)?->id,
                'numero_dia' => 3,
                'hora_inicio' => '2025-06-11 08:00:00',
                'hora_fin' => '2025-06-11 10:00:00',
            ],
            // Día 3 — franja 10:30-12:00
            [
                'aula_id' => $aulas->get(201)?->id,
                'numero_dia' => 3,
                'hora_inicio' => '2025-06-11 10:30:00',
                'hora_fin' => '2025-06-11 12:00:00',
            ],
            // Día 3 — Clausura
            [
                'aula_id' => $auditorio?->id,
                'numero_dia' => 3,
                'hora_inicio' => '2025-06-11 14:00:00',
                'hora_fin' => '2025-06-11 16:00:00',
            ],
        ];

        foreach ($horarios as $horario) {
            Horario::create($horario);
        }
    }
}
