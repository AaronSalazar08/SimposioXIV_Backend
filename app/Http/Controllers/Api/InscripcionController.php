<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InscripcionResource;
use App\Models\Inscripcion;
use App\Services\InscripcionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InscripcionController extends Controller
{
    public function __construct(private readonly InscripcionService $inscripcionService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $inscripciones = $this->inscripcionService->listarDeUsuario($request->user()->id);

        return InscripcionResource::collection($inscripciones);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'evento_id' => ['required', 'integer', 'exists:eventos,id'],
        ]);

        $inscripcion = $this->inscripcionService->inscribir(
            $request->user()->id,
            (int) $request->evento_id,
        );

        return response()->json([
            'message' => 'Inscripción registrada correctamente.',
            'data' => new InscripcionResource($inscripcion),
        ], 201);
    }

    public function destroy(Request $request, Inscripcion $inscripcion): JsonResponse
    {
        $this->inscripcionService->cancelar($inscripcion, $request->user()->id);

        return response()->json(['message' => 'Inscripción cancelada correctamente.']);
    }
}
