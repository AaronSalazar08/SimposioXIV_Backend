<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $identifier, string $password, bool $isEmail): array
    {
        $user = $isEmail
            ? User::where('email', $identifier)->first()
            : User::where('carnet', $identifier)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            Log::warning('Intento de inicio de sesión fallido.', [
                'identifier' => $identifier,
            ]);

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
