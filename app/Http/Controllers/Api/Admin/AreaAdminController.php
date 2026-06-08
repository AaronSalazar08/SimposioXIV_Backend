<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Models\Area;
use App\Services\AreaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AreaAdminController extends Controller
{
    public function __construct(private readonly AreaService $areaService) {}

    public function index(): AnonymousResourceCollection
    {
        return AreaResource::collection($this->areaService->listar());
    }

    public function show(Area $area): JsonResponse
    {
        return response()->json(['data' => new AreaResource($area)]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $area = $this->areaService->crear($datos);

        return response()->json([
            'message' => 'Área creada correctamente.',
            'data' => new AreaResource($area),
        ], 201);
    }

    public function update(Request $request, Area $area): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:100'],
            'descripcion' => ['sometimes', 'nullable', 'string'],
            'color' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        $actualizada = $this->areaService->actualizar($area, $datos);

        return response()->json([
            'message' => 'Área actualizada correctamente.',
            'data' => new AreaResource($actualizada),
        ]);
    }

    public function destroy(Area $area): JsonResponse
    {
        $this->areaService->eliminar($area);

        return response()->json(['message' => 'Área eliminada correctamente.']);
    }
}
