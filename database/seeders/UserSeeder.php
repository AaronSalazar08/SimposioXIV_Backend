<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuarios de prueba con credenciales conocidas para desarrollo
        $usuarios = [
            [
                'nombre' => 'Aaron Salazar',
                'email' => 'aaron.salazarmata@ucr.ac.cr',
                'carnet' => 'C37190',
                'password' => Hash::make('password'),
            ],
            [
                'nombre' => 'María Rodríguez',
                'email' => 'maria.rodriguez@ucr.ac.cr',
                'carnet' => 'C23456',
                'password' => Hash::make('password'),
            ],
            [
                'nombre' => 'Carlos Mora',
                'email' => 'carlos.mora@ucr.ac.cr',
                'carnet' => 'C34567',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($usuarios as $usuario) {
            User::create($usuario);
        }

        // Usuarios adicionales aleatorios para pruebas de carga

    }
}
