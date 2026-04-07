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
     * - El email debe ser @ucr.ac.cr
     * - El carnet debe tener exactamente 6 caracteres alfanuméricos
     * - Las credenciales email + password deben coincidir
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'regex:/@ucr\.ac\.cr$/'],
            'carnet' => ['required', 'string', 'size:6', 'alpha_num'],
            'password' => ['required', 'string'],
        ], [
            'email.regex' => 'Solo se permiten correos institucionales (@ucr.ac.cr).',
            'carnet.size' => 'El carnet debe tener exactamente 6 caracteres.',
            'carnet.alpha_num' => 'El carnet solo puede contener letras y números.',
        ]);

        $user = User::where('email', $request->email)
            ->where('carnet', $request->carnet)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
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
