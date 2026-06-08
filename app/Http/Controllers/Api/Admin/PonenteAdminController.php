<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PonenteResource;
use App\Models\Ponente;
use App\Services\PonenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PonenteAdminController extends Controller
{
    public function __construct(private readonly PonenteService $ponenteService) {}

    public function index(): AnonymousResourceCollection
    {
        return PonenteResource::collection($this->ponenteService->listar());
    }

    public function show(Ponente $ponente): JsonResponse
    {
        return response()->json(['data' => new PonenteResource($ponente)]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'educacion' => ['nullable', 'string'],
            'grado_academico' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $ponente = $this->ponenteService->crear($datos);

        return response()->json([
            'message' => 'Ponente creado correctamente.',
            'data' => new PonenteResource($ponente),
        ], 201);
    }

    public function update(Request $request, Ponente $ponente): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:100'],
            'apellidos' => ['sometimes', 'string', 'max:100'],
            'educacion' => ['sometimes', 'nullable', 'string'],
            'grado_academico' => ['sometimes', 'nullable', 'string', 'max:100'],
            'descripcion' => ['sometimes', 'nullable', 'string'],
        ]);

        $actualizado = $this->ponenteService->actualizar($ponente, $datos);

        return response()->json([
            'message' => 'Ponente actualizado correctamente.',
            'data' => new PonenteResource($actualizado),
        ]);
    }

    public function destroy(Ponente $ponente): JsonResponse
    {
        $this->ponenteService->eliminar($ponente);

        return response()->json(['message' => 'Ponente eliminado correctamente.']);
    }
}
