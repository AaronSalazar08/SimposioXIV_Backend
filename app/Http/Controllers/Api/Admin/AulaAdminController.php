<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AulaResource;
use App\Models\Aula;
use App\Services\AulaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AulaAdminController extends Controller
{
    public function __construct(private readonly AulaService $aulaService) {}

    public function index(): AnonymousResourceCollection
    {
        return AulaResource::collection($this->aulaService->listar());
    }

    public function show(Aula $aula): JsonResponse
    {
        return response()->json(['data' => new AulaResource($aula)]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'numero' => ['required', 'string', 'max:20'],
            'edificio' => ['required', 'string', 'max:100'],
            'capacidad' => ['required', 'integer', 'min:1'],
        ]);

        $aula = $this->aulaService->crear($datos);

        return response()->json([
            'message' => 'Aula creada correctamente.',
            'data' => new AulaResource($aula),
        ], 201);
    }

    public function update(Request $request, Aula $aula): JsonResponse
    {
        $datos = $request->validate([
            'numero' => ['sometimes', 'string', 'max:20'],
            'edificio' => ['sometimes', 'string', 'max:100'],
            'capacidad' => ['sometimes', 'integer', 'min:1'],
        ]);

        $actualizada = $this->aulaService->actualizar($aula, $datos);

        return response()->json([
            'message' => 'Aula actualizada correctamente.',
            'data' => new AulaResource($actualizada),
        ]);
    }

    public function destroy(Aula $aula): JsonResponse
    {
        $this->aulaService->eliminar($aula);

        return response()->json(['message' => 'Aula eliminada correctamente.']);
    }
}
