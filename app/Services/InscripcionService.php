<?php

namespace App\Services;

use App\Enums\EstadoInscripcion;
use App\Models\Evento;
use App\Models\Inscripcion;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class InscripcionService
{
    public function listarDeUsuario(int $userId): Collection
    {
        return Inscripcion::query()
            ->with(['evento.horario.aula', 'evento.ponente', 'evento.areas'])
            ->where('user_id', $userId)
            ->orderByDesc('enrolled_at')
            ->get();
    }

    public function inscribir(int $userId, int $eventoId): Inscripcion
    {
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

        return $inscripcion;
    }

    public function cancelar(Inscripcion $inscripcion, int $userId): void
    {
        if ($inscripcion->user_id !== $userId) {
            throw new AuthorizationException('No tienes permiso para cancelar esta inscripción.');
        }

        if ($inscripcion->estado === EstadoInscripcion::Cancelado) {
            throw new ConflictHttpException('La inscripción ya estaba cancelada.');
        }

        DB::transaction(function () use ($inscripcion) {
            $evento = Evento::lockForUpdate()->find($inscripcion->evento_id);

            $inscripcion->update(['estado' => EstadoInscripcion::Cancelado]);

            if ($evento && $evento->numero_inscritos > 0) {
                $evento->decrement('numero_inscritos');
            }
        });
    }
}
