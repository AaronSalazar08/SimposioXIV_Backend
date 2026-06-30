<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CambiarPasswordRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    public function enviarOtp(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->otpService->enviar($user);

        return response()->json(['message' => 'Código enviado a tu correo electrónico.']);
    }

    public function verificarOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'digits:6'],
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.digits' => 'El código debe tener exactamente 6 dígitos.',
        ]);

        /** @var User $user */
        $user = $request->user();

        if (! $this->otpService->verificar($user, $data['codigo'])) {
            throw ValidationException::withMessages([
                'codigo' => ['El código es incorrecto o ha expirado. Solicitá uno nuevo.'],
            ]);
        }

        return response()->json(['message' => 'Código verificado correctamente.']);
    }

    public function cambiarPassword(CambiarPasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->otpService->estaVerificado($user)) {
            throw ValidationException::withMessages([
                'codigo' => ['Debés verificar el código OTP antes de cambiar la contraseña.'],
            ]);
        }

        $user->update(['password' => $request->string('password')->toString()]);
        $this->otpService->limpiarVerificacion($user);

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
}
