<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventoResource;
use App\Models\Evento;
use App\Services\EventoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventoController extends Controller
{
    public function __construct(private readonly EventoService $eventoService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filtros = $request->only(['dia', 'tipo', 'area_id', 'solo_disponibles']);
        $eventos = $this->eventoService->listar($filtros, $request->user()?->id);

        return EventoResource::collection($eventos);
    }

    public function show(Request $request, Evento $evento): JsonResponse
    {
        $evento = $this->eventoService->mostrar($evento, $request->user()?->id);

        return response()->json(['data' => new EventoResource($evento)]);
    }
}
