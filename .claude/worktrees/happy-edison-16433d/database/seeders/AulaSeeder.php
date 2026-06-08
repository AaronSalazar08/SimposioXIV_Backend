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
            ['numero' => 101, 'edificio' => 'Edificio Principal', 'capacidad' => 40],
            ['numero' => 102, 'edificio' => 'Edificio Principal', 'capacidad' => 30],
            ['numero' => 201, 'edificio' => 'Edificio Principal', 'capacidad' => 50],
            ['numero' => 202, 'edificio' => 'Edificio Principal', 'capacidad' => 30],
            ['numero' => 301, 'edificio' => 'Auditorio', 'capacidad' => 200],
            ['numero' => 105, 'edificio' => 'Edificio Ciencias', 'capacidad' => 25],
            ['numero' => 106, 'edificio' => 'Edificio Ciencias', 'capacidad' => 25],
        ];

        foreach ($aulas as $aula) {
            Aula::create($aula);
        }
    }
}
