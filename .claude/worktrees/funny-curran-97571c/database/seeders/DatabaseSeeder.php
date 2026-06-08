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
            UserSeeder::class,   // primero: no depende de otros
            AulaSeeder::class,   // primero: no depende de otros
            AreaSeeder::class,   // primero: no depende de otros
            EventoSeeder::class, // depende de: aulas, areas
        ]);
    }
}
