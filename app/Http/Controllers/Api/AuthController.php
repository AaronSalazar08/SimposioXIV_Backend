<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $resultado = $this->authService->login(
            $request->identifier(),
            $request->string('password')->toString(),
            $request->isEmailIdentifier(),
        );

        return response()->json([
            'token' => $resultado['token'],
            'user' => new UserResource($resultado['user']),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    public function me(Request $request): JsonResource
    {
        return new UserResource($request->user());
    }
}
