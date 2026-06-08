<?php

namespace Database\Seeders;

use App\Enums\TipoUsuario;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ucr.ac.cr'],
            [
                'nombre' => 'Administrador',
                'password' => 'Admin1234!',
                'tipo_usuario' => TipoUsuario::Admin->value,
            ],
        );

        $this->command->info('Admin creado: admin@ucr.ac.cr / Admin1234!');
    }
}
