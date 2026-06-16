<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoInscripcion;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexInscripcionRequest;
use App\Http\Requests\StoreInscripcionRequest;
use App\Http\Resources\InscripcionResource;
use App\Models\Inscripcion;
use App\Services\InscripcionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class InscripcionController extends Controller
{
    public function __construct(private readonly InscripcionService $inscripcionService) {}

    public function index(IndexInscripcionRequest $request): AnonymousResourceCollection
    {
        $estado = $request->filled('estado')
            ? EstadoInscripcion::from($request->string('estado')->toString())
            : null;

        $inscripciones = $this->inscripcionService->listarDeUsuario(
            $request->user()->id,
            $estado,
        );

        return InscripcionResource::collection($inscripciones);
    }

    public function store(StoreInscripcionRequest $request): JsonResponse
    {
        $inscripcion = $this->inscripcionService->inscribir(
            $request->user()->id,
            $request->integer('evento_id'),
        );

        return response()->json([
            'message' => 'Inscripción registrada correctamente.',
            'data' => new InscripcionResource($inscripcion),
        ], Response::HTTP_CREATED);
    }

    public function destroy(Inscripcion $inscripcion): JsonResponse
    {
        $this->authorize('delete', $inscripcion);

        try {
            $this->inscripcionService->cancelar($inscripcion);
        } catch (ConflictHttpException $e) {
            return ApiResponse::error(
                $e->getMessage(),
                Response::HTTP_CONFLICT,
                ['inscripcion' => [$e->getMessage()]],
            );
        }

        return response()->json(['message' => 'Inscripción cancelada correctamente.']);
    }
}
