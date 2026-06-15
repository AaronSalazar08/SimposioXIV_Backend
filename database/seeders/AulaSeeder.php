<?php

namespace Database\Seeders;

use App\Models\Aula;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AulaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aulas = [
            // Venues del Día 1
            ['numero' => 1,   'edificio' => 'Hotel Las Espuelas',    'capacidad' => 300],
            ['numero' => 1,   'edificio' => 'UCR - Salón Multiusos', 'capacidad' => 150],
            ['numero' => 1,   'edificio' => 'Gimnasio UCR',          'capacidad' => 200],
            // Aulas de días 2 y 3
            ['numero' => 101, 'edificio' => 'Edificio Principal', 'capacidad' => 50],
            ['numero' => 102, 'edificio' => 'Edificio Principal', 'capacidad' => 30],
            ['numero' => 201, 'edificio' => 'Edificio Principal', 'capacidad' => 50],
            ['numero' => 202, 'edificio' => 'Edificio Principal', 'capacidad' => 30],
            ['numero' => 301, 'edificio' => 'Auditorio',          'capacidad' => 200],
            ['numero' => 105, 'edificio' => 'Edificio Ciencias',  'capacidad' => 25],
            ['numero' => 106, 'edificio' => 'Edificio Ciencias',  'capacidad' => 25],
        ];

        foreach ($aulas as $aula) {
            Aula::create($aula);
        }
    }
}
