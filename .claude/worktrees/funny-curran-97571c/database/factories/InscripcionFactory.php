<?php

namespace Database\Factories;

use App\Enums\EstadoInscripcion;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inscripcion>
 */
class InscripcionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'evento_id' => Evento::factory(),
            'estado' => EstadoInscripcion::Pendiente,
            'enrolled_at' => now(),
        ];
    }

    public function confirmada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoInscripcion::Confirmado,
        ]);
    }

    public function cancelada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoInscripcion::Cancelado,
        ]);
    }
}
