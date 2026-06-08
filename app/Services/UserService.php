<?php

namespace App\Services;

use App\Enums\TipoUsuario;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class UserService
{
    public function listar(): Collection
    {
        return User::orderBy('nombre')->get();
    }

    public function crear(array $datos): array
    {
        $passwordPlano = $datos['password'] ?? $this->generarPassword();

        $user = User::create([
            'nombre' => $datos['nombre'],
            'email' => $datos['email'],
            'carnet' => $datos['carnet'] ?? null,
            'password' => $passwordPlano,
            'tipo_usuario' => $datos['tipo_usuario'] ?? TipoUsuario::Participante->value,
        ]);

        return ['user' => $user, 'password' => $passwordPlano];
    }

    public function actualizar(User $user, array $datos): User
    {
        $campos = array_filter([
            'nombre' => $datos['nombre'] ?? null,
            'email' => $datos['email'] ?? null,
            'carnet' => array_key_exists('carnet', $datos) ? $datos['carnet'] : $user->carnet,
            'tipo_usuario' => $datos['tipo_usuario'] ?? null,
        ], fn ($v) => $v !== null);

        if (isset($datos['password'])) {
            $campos['password'] = $datos['password'];
        }

        $user->update($campos);
        $user->refresh();

        return $user;
    }

    public function eliminar(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }

    public function generarPassword(int $longitud = 12): string
    {
        return Str::password($longitud, symbols: false);
    }
}
