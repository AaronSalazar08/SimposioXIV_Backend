<?php

namespace Database\Factories;

use App\Enums\TipoEvento;
use App\Models\Evento;
use App\Models\Horario;
use App\Models\Ponente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evento>
 */
class EventoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'horario_id' => Horario::factory(),
            'ponente_id' => Ponente::factory(),
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'tipo' => fake()->randomElement(TipoEvento::cases()),
            'capacidad' => fake()->randomElement([20, 30, 40]),
            'numero_inscritos' => 0,
            'esta_activo' => true,
        ];
    }

    public function taller(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoEvento::Taller,
        ]);
    }

    public function charla(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoEvento::Charla,
        ]);
    }

    public function apertura(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoEvento::Apertura,
            'capacidad' => 200,
        ]);
    }

    public function clausura(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoEvento::Clausura,
            'capacidad' => 200,
        ]);
    }

    public function sinCupos(): static
    {
        return $this->state(function (array $attributes) {
            $capacidad = $attributes['capacidad'] ?? 10;

            return [
                'capacidad' => $capacidad,
                'numero_inscritos' => $capacidad,
            ];
        });
    }
}
