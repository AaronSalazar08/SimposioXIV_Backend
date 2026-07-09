<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\HorarioResource;
use App\Models\Horario;
use App\Services\HorarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HorarioAdminController extends Controller
{
    public function __construct(private readonly HorarioService $horarioService) {}

    public function index(): AnonymousResourceCollection
    {
        return HorarioResource::collection($this->horarioService->listar());
    }

    public function show(Horario $horario): JsonResponse
    {
        $horario->load('aula');

        return response()->json(['data' => new HorarioResource($horario)]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'aula_id' => ['nullable', 'integer', 'exists:aulas,id'],
            'numero_dia' => ['required', 'integer', 'in:1,2,3'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ]);

        $horario = $this->horarioService->crear($datos);

        return response()->json([
            'message' => 'Horario creado correctamente.',
            'data' => new HorarioResource($horario),
        ], 201);
    }

    public function update(Request $request, Horario $horario): JsonResponse
    {
        $datos = $request->validate([
            'aula_id' => ['sometimes', 'nullable', 'integer', 'exists:aulas,id'],
            'numero_dia' => ['sometimes', 'integer', 'in:1,2,3'],
            'hora_inicio' => ['sometimes', 'date_format:H:i'],
            'hora_fin' => ['sometimes', 'date_format:H:i', 'after:hora_inicio'],
        ]);

        $actualizado = $this->horarioService->actualizar($horario, $datos);

        return response()->json([
            'message' => 'Horario actualizado correctamente.',
            'data' => new HorarioResource($actualizado),
        ]);
    }

    public function destroy(Horario $horario): JsonResponse
    {
        $this->horarioService->eliminar($horario);

        return response()->json(['message' => 'Horario eliminado correctamente.']);
    }
}
