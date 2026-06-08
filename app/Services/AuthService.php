<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $identifier, string $password): array
    {
        $isEmail = str_contains($identifier, '@');

        if ($isEmail) {
            if (! preg_match('/@ucr\.ac\.cr$/', $identifier)) {
                throw ValidationException::withMessages([
                    'identifier' => ['Solo se permiten correos institucionales (@ucr.ac.cr).'],
                ]);
            }

            $user = User::where('email', $identifier)->first();
        } else {
            if (! preg_match('/^[a-zA-Z0-9]{6}$/', $identifier)) {
                throw ValidationException::withMessages([
                    'identifier' => ['El carnet debe tener exactamente 6 caracteres alfanuméricos.'],
                ]);
            }

            $user = User::where('carnet', $identifier)->first();
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $user->createToken('simposio-token')->plainTextToken;

        return ['token' => $token, 'user' => $user];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
