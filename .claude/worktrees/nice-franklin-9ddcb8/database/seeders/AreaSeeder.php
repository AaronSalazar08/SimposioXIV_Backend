<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'nombre' => 'Inteligencia Artificial',
                'descripcion' => 'Machine Learning, Deep Learning, modelos de lenguaje y aplicaciones de IA.',
                'color' => '#6366F1',
            ],
            [
                'nombre' => 'Ciberseguridad',
                'descripcion' => 'Seguridad ofensiva, defensiva, ethical hacking y protección de datos.',
                'color' => '#EF4444',
            ],
            [
                'nombre' => 'Desarrollo Web',
                'descripcion' => 'Frontend, backend, APIs REST, frameworks modernos y buenas prácticas.',
                'color' => '#3B82F6',
            ],
            [
                'nombre' => 'Bases de Datos',
                'descripcion' => 'SQL, NoSQL, optimización de consultas y modelado de datos.',
                'color' => '#F59E0B',
            ],
            [
                'nombre' => 'Cloud Computing',
                'descripcion' => 'AWS, GCP, Azure, contenedores y arquitecturas en la nube.',
                'color' => '#10B981',
            ],
            [
                'nombre' => 'Ciencia de Datos',
                'descripcion' => 'Análisis de datos, visualización y estadística aplicada.',
                'color' => '#8B5CF6',
            ],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}
