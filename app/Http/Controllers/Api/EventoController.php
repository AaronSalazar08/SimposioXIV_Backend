<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventoResource;
use App\Models\Evento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventoController extends Controller
{
    /**
     * Lista los eventos del simposio.
     *
     * Filtros opcionales por query string:
     * - dia: filtra por numero_dia del horario (1, 2, 3)
     * - tipo: filtra por TipoEvento (apertura, clausura, taller, charla)
     * - area_id: filtra eventos asociados a un área específica
     * - solo_disponibles: si es "1"/"true", solo retorna eventos con cupos
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Evento::query()
            ->with([
                'horario.aula',
                'ponente',
                'areas',
                'inscripciones' => fn ($q) => $q->where('user_id', $request->user()?->id),
            ])
            ->where('esta_activo', true);

        if ($request->filled('dia')) {
            $query->whereHas('horario', fn ($q) => $q->where('numero_dia', (int) $request->dia));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('area_id')) {
            $query->whereHas('areas', fn ($q) => $q->where('areas.id', (int) $request->area_id));
        }

        if ($request->boolean('solo_disponibles')) {
            $query->whereColumn('numero_inscritos', '<', 'capacidad');
        }

        $eventos = $query
            ->join('horarios', 'horarios.id', '=', 'eventos.horario_id')
            ->orderBy('horarios.hora_inicio')
            ->select('eventos.*')
            ->get();

        return EventoResource::collection($eventos);
    }

    /**
     * Devuelve el detalle de un evento, incluyendo horario, aula, ponente y áreas.
     */
    public function show(Request $request, Evento $evento): JsonResponse
    {
        $evento->load([
            'horario.aula',
            'ponente',
            'areas',
            'inscripciones' => fn ($q) => $q->where('user_id', $request->user()?->id),
        ]);

        return response()->json([
            'data' => new EventoResource($evento),
        ]);
    }
}
