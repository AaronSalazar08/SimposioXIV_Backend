<?php

namespace Database\Factories;

use App\Models\Aula;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aula>
 */
class AulaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero' => fake()->unique()->numberBetween(100, 999),
            'edificio' => fake()->randomElement(['Edificio A', 'Edificio B', 'Edificio C', 'Edificio Ciencias']),
            'capacidad' => fake()->randomElement([20, 30, 40, 50, 60]),
        ];
    }
}
