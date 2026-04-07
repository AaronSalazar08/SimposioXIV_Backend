<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Inicia sesión con credenciales UCR.
     *
     * Reglas de negocio:
     * - Se debe enviar "identifier" (email @ucr.ac.cr O carnet de 6 caracteres) + "password"
     * - Si el identifier contiene "@" se trata como email, de lo contrario como carnet
     * - El email debe ser @ucr.ac.cr
     * - El carnet debe tener exactamente 6 caracteres alfanuméricos
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = $request->identifier;
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

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $user->createToken('simposio-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Cierra la sesión revocando el token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Devuelve el usuario autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
