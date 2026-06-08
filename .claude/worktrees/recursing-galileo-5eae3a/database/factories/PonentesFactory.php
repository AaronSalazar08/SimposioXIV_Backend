<?php

namespace Database\Factories;

use App\Models\Ponente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ponente>
 */
class PonentesFactory extends Factory
{
    protected $model = Ponente::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName().' '.fake()->lastName(),
            'educacion' => fake()->randomElement([
                'Universidad de Costa Rica',
                'Instituto Tecnológico de Costa Rica',
                'Universidad Nacional de Costa Rica',
                'UNED Costa Rica',
            ]),
            'grado_academico' => fake()->randomElement(['Licenciado', 'Magíster', 'Doctor']),
            'descripcion' => fake()->paragraph(),
        ];
    }

    public function doctor(): static
    {
        return $this->state(fn (array $attributes) => [
            'grado_academico' => 'Doctor',
        ]);
    }

    public function magister(): static
    {
        return $this->state(fn (array $attributes) => [
            'grado_academico' => 'Magíster',
        ]);
    }

    public function licenciado(): static
    {
        return $this->state(fn (array $attributes) => [
            'grado_academico' => 'Licenciado',
        ]);
    }
}
