<?php

namespace Database\Factories;

use App\Models\Aula;
use App\Models\Evento;
use App\Models\Horario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Horario>
 */
class HorarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $horaInicio = fake()->dateTimeBetween('2025-06-01 08:00:00', '2025-06-03 16:00:00');
        $horaFin = (clone $horaInicio)->modify('+2 hours');

        return [
            'evento_id' => Evento::factory(),
            'aula_id' => Aula::factory(),
            'numero_dia' => fake()->numberBetween(1, 3),
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
        ];
    }
}
