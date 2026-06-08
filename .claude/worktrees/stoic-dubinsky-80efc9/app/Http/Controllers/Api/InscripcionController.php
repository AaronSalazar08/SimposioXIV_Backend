<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoInscripcion;
use App\Http\Controllers\Controller;
use App\Http\Resources\InscripcionResource;
use App\Models\Evento;
use App\Models\Inscripcion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InscripcionController extends Controller
{
    /**
     * Lista las inscripciones del usuario autenticado.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $inscripciones = Inscripcion::query()
            ->with(['evento.horario.aula', 'evento.ponente', 'evento.areas'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('enrolled_at')
            ->get();

        return InscripcionResource::collection($inscripciones);
    }

    /**
     * Inscribe al usuario autenticado en un evento.
     *
     * Reglas de negocio:
     * - El evento debe existir y estar activo.
     * - Debe haber capacidad disponible (numero_inscritos < capacidad).
     * - El usuario no puede inscribirse dos veces al mismo evento.
     * - El usuario no puede tener otra inscripción activa que se solape en horario.
     * - Se incrementa atómicamente numero_inscritos dentro de una transacción con bloqueo.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'evento_id' => ['required', 'integer', 'exists:eventos,id'],
        ]);

        $userId = $request->user()->id;
        $eventoId = (int) $request->evento_id;

        $inscripcion = DB::transaction(function () use ($userId, $eventoId) {
            $evento = Evento::with('horario')
                ->lockForUpdate()
                ->findOrFail($eventoId);

            if (! $evento->esta_activo) {
                throw ValidationException::withMessages([
                    'evento_id' => ['El evento no está activo.'],
                ]);
            }

            if (! $evento->tieneCapacidadDisponible()) {
                throw ValidationException::withMessages([
                    'evento_id' => ['El evento ya no tiene cupos disponibles.'],
                ]);
            }

            $existente = Inscripcion::where('user_id', $userId)
                ->where('evento_id', $eventoId)
                ->first();

            if ($existente && $existente->estado === EstadoInscripcion::Confirmado) {
                throw ValidationException::withMessages([
                    'evento_id' => ['Ya estás inscrito en este evento.'],
                ]);
            }

            $solape = Inscripcion::query()
                ->where('inscripciones.user_id', $userId)
                ->where('inscripciones.estado', EstadoInscripcion::Confirmado->value)
                ->join('eventos', 'eventos.id', '=', 'inscripciones.evento_id')
                ->join('horarios', 'horarios.id', '=', 'eventos.horario_id')
                ->where('horarios.hora_inicio', '<', $evento->horario->hora_fin)
                ->where('horarios.hora_fin', '>', $evento->horario->hora_inicio)
                ->exists();

            if ($solape) {
                throw ValidationException::withMessages([
                    'evento_id' => ['Ya tienes una inscripción activa que se solapa con este horario.'],
                ]);
            }

            if ($existente) {
                $existente->update([
                    'estado' => EstadoInscripcion::Confirmado,
                    'enrolled_at' => now(),
                ]);
                $inscripcion = $existente;
            } else {
                $inscripcion = Inscripcion::create([
                    'user_id' => $userId,
                    'evento_id' => $eventoId,
                    'estado' => EstadoInscripcion::Confirmado,
                    'enrolled_at' => now(),
                ]);
            }

            $evento->increment('numero_inscritos');

            return $inscripcion;
        });

        $inscripcion->load(['evento.horario.aula', 'evento.ponente', 'evento.areas']);

        return response()->json([
            'message' => 'Inscripción registrada correctamente.',
            'data' => new InscripcionResource($inscripcion),
        ], 201);
    }

    /**
     * Cancela una inscripción del usuario autenticado y libera el cupo.
     */
    public function destroy(Request $request, Inscripcion $inscripcion): JsonResponse
    {
        if ($inscripcion->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para cancelar esta inscripción.',
            ], 403);
        }

        if ($inscripcion->estado === EstadoInscripcion::Cancelado) {
            return response()->json([
                'message' => 'La inscripción ya estaba cancelada.',
            ], 409);
        }

        DB::transaction(function () use ($inscripcion) {
            $evento = Evento::lockForUpdate()->find($inscripcion->evento_id);

            $inscripcion->update(['estado' => EstadoInscripcion::Cancelado]);

            if ($evento && $evento->numero_inscritos > 0) {
                $evento->decrement('numero_inscritos');
            }
        });

        return response()->json([
            'message' => 'Inscripción cancelada correctamente.',
        ]);
    }
}
