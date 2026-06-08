<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement([
                'Inteligencia Artificial',
                'Ciberseguridad',
                'Desarrollo Web',
                'Bases de Datos',
                'Cloud Computing',
                'Redes',
                'Ciencia de Datos',
            ]),
            'descripcion' => fake()->sentence(),
            'color' => fake()->hexColor(),
        ];
    }
}
