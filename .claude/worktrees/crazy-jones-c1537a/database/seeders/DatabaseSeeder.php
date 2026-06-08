<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,     // sin dependencias
            AulaSeeder::class,     // sin dependencias
            AreaSeeder::class,     // sin dependencias
            PonentesSeeder::class, // sin dependencias
            HorarioSeeder::class,  // depende de: aulas
            EventoSeeder::class,   // depende de: horarios, ponentes, areas
        ]);
    }
}
