<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TipoUsuario;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserAdminController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection($this->userService->listar());
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:45'],
            'email' => ['required', 'email', 'unique:users,email'],
            'carnet' => ['nullable', 'string', 'size:6', 'alpha_num', 'unique:users,carnet'],
            'password' => ['nullable', 'string', 'min:8'],
            'tipo_usuario' => ['nullable', new Enum(TipoUsuario::class)],
        ]);

        $resultado = $this->userService->crear($datos);

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'data' => new UserResource($resultado['user']),
            'password_generada' => $resultado['password'],
        ], 201);
    }

    public function show(User $usuario): JsonResponse
    {
        return response()->json(['data' => new UserResource($usuario)]);
    }

    public function update(Request $request, User $usuario): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:45'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($usuario->id)],
            'carnet' => ['sometimes', 'nullable', 'string', 'size:6', 'alpha_num', Rule::unique('users', 'carnet')->ignore($usuario->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'tipo_usuario' => ['sometimes', new Enum(TipoUsuario::class)],
        ]);

        $actualizado = $this->userService->actualizar($usuario, $datos);

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data' => new UserResource($actualizado),
        ]);
    }

    public function destroy(User $usuario): JsonResponse
    {
        $this->userService->eliminar($usuario);

        return response()->json(['message' => 'Usuario eliminado correctamente.']);
    }

    public function generarPassword(): JsonResponse
    {
        return response()->json(['password' => $this->userService->generarPassword()]);
    }
}
