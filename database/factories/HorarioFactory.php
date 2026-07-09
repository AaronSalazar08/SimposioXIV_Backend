<?php

namespace Database\Factories;

use App\Models\Aula;
use App\Models\Horario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Horario>
 */
class HorarioFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $horaInicio = fake()->dateTimeBetween('2025-06-09 08:00:00', '2025-06-11 14:00:00');
        $horaFin = (clone $horaInicio)->modify('+2 hours');

        return [
            'aula_id' => Aula::factory(),
            'numero_dia' => fake()->numberBetween(1, 3),
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
        ];
    }

    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'aula_id' => null,
        ]);
    }
}
