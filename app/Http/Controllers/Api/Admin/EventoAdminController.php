<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TipoEvento;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventoResource;
use App\Http\Resources\InscripcionResource;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Services\EventoAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Enum;

class EventoAdminController extends Controller
{
    public function __construct(private readonly EventoAdminService $eventoAdminService) {}

    public function index(): AnonymousResourceCollection
    {
        return EventoResource::collection($this->eventoAdminService->listar());
    }

    public function show(Evento $evento): JsonResponse
    {
        return response()->json(['data' => new EventoResource($this->eventoAdminService->mostrar($evento))]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', new Enum(TipoEvento::class)],
            'capacidad' => ['required', 'integer', 'min:1'],
            'esta_activo' => ['boolean'],
            'horario_id' => ['required', 'integer', 'exists:horarios,id'],
            'ponente_ids' => ['nullable', 'array'],
            'ponente_ids.*' => ['integer', 'exists:ponentes,id'],
            'area_ids' => ['nullable', 'array'],
            'area_ids.*' => ['integer', 'exists:areas,id'],
        ]);

        $evento = $this->eventoAdminService->crear($datos);

        return response()->json([
            'message' => 'Evento creado correctamente.',
            'data' => new EventoResource($evento),
        ], 201);
    }

    public function update(Request $request, Evento $evento): JsonResponse
    {
        $datos = $request->validate([
            'titulo' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'nullable', 'string'],
            'tipo' => ['sometimes', new Enum(TipoEvento::class)],
            'capacidad' => ['sometimes', 'integer', 'min:1'],
            'esta_activo' => ['sometimes', 'boolean'],
            'horario_id' => ['sometimes', 'integer', 'exists:horarios,id'],
            'ponente_ids' => ['sometimes', 'nullable', 'array'],
            'ponente_ids.*' => ['integer', 'exists:ponentes,id'],
            'area_ids' => ['sometimes', 'nullable', 'array'],
            'area_ids.*' => ['integer', 'exists:areas,id'],
        ]);

        $actualizado = $this->eventoAdminService->actualizar($evento, $datos);

        return response()->json([
            'message' => 'Evento actualizado correctamente.',
            'data' => new EventoResource($actualizado),
        ]);
    }

    public function destroy(Evento $evento): JsonResponse
    {
        $this->eventoAdminService->eliminar($evento);

        return response()->json(['message' => 'Evento eliminado correctamente.']);
    }

    public function inscritos(Evento $evento): AnonymousResourceCollection
    {
        return InscripcionResource::collection($this->eventoAdminService->inscritos($evento));
    }

    public function actualizarAsistencia(Request $request, Evento $evento, Inscripcion $inscripcion): JsonResponse
    {
        abort_if($inscripcion->evento_id !== $evento->id, 404);

        $datos = $request->validate(['asistio' => ['required', 'boolean']]);

        $actualizada = $this->eventoAdminService->marcarAsistencia($inscripcion, $datos['asistio']);

        return response()->json(['data' => new InscripcionResource($actualizada)]);
    }
}
