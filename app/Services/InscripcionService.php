<?php

namespace App\Services;

use App\Enums\EstadoInscripcion;
use App\Exceptions\Inscripcion\CupoAgotadoException;
use App\Exceptions\Inscripcion\EventoInactivoException;
use App\Exceptions\Inscripcion\HorarioSolapadoException;
use App\Exceptions\Inscripcion\InscripcionDuplicadaException;
use App\Models\Evento;
use App\Models\Inscripcion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class InscripcionService
{
    public function listarDeUsuario(int $userId, ?EstadoInscripcion $estado = null): Collection
    {
        $query = Inscripcion::query()
            ->with(['evento.horario.aula', 'evento.ponentes', 'evento.areas'])
            ->delUsuario($userId)
            ->orderByDesc('enrolled_at');

        if ($estado !== null) {
            $query->conEstado($estado);
        }

        return $query->get();
    }

    public function inscribir(int $userId, int $eventoId): Inscripcion
    {
        $inscripcion = DB::transaction(function () use ($userId, $eventoId) {
            $evento = Evento::with('horario')
                ->lockForUpdate()
                ->findOrFail($eventoId);

            if (! $evento->esta_activo) {
                throw new EventoInactivoException;
            }

            if (! $evento->tieneCapacidadDisponible()) {
                throw new CupoAgotadoException;
            }

            $existente = Inscripcion::query()
                ->delUsuario($userId)
                ->where('evento_id', $eventoId)
                ->first();

            if ($existente && $existente->estado === EstadoInscripcion::Confirmado) {
                throw new InscripcionDuplicadaException;
            }

            if (Inscripcion::tieneSolapeDeHorario($userId, $evento->horario)) {
                throw new HorarioSolapadoException;
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

        $inscripcion->load(['evento.horario.aula', 'evento.ponentes', 'evento.areas']);

        return $inscripcion;
    }

    public function cancelar(Inscripcion $inscripcion): void
    {
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
