<?php

namespace App\Services;

use App\Enums\EstadoInscripcion;
use App\Models\Evento;
use App\Models\Inscripcion;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class EventoAdminService
{
    public function listar(): Collection
    {
        return Evento::with(['horario.aula', 'ponentes', 'areas'])
            ->join('horarios', 'horarios.id', '=', 'eventos.horario_id')
            ->orderBy('horarios.hora_inicio')
            ->select('eventos.*')
            ->get();
    }

    public function mostrar(Evento $evento): Evento
    {
        $evento->load(['horario.aula', 'ponentes', 'areas']);

        return $evento;
    }

    public function crear(array $datos): Evento
    {
        $areaIds = $datos['area_ids'] ?? [];
        unset($datos['area_ids']);

        $ponenteIds = $datos['ponente_ids'] ?? [];
        unset($datos['ponente_ids']);

        $evento = Evento::create($datos);

        if (! empty($areaIds)) {
            $evento->areas()->sync($areaIds);
        }

        if (! empty($ponenteIds)) {
            $evento->ponentes()->sync($ponenteIds);
        }

        return $evento->fresh(['horario.aula', 'ponentes', 'areas']);
    }

    public function actualizar(Evento $evento, array $datos): Evento
    {
        $areaIds = array_key_exists('area_ids', $datos) ? $datos['area_ids'] : null;
        unset($datos['area_ids']);

        $ponenteIds = array_key_exists('ponente_ids', $datos) ? $datos['ponente_ids'] : null;
        unset($datos['ponente_ids']);

        $evento->update($datos);

        if ($areaIds !== null) {
            $evento->areas()->sync($areaIds);
        }

        if ($ponenteIds !== null) {
            $evento->ponentes()->sync($ponenteIds);
        }

        return $evento->fresh(['horario.aula', 'ponentes', 'areas']);
    }

    public function eliminar(Evento $evento): void
    {
        $totalInscritos = $evento->inscripciones()->conEstado(EstadoInscripcion::Confirmado)->count();

        if ($totalInscritos > 0) {
            throw new ConflictHttpException(
                "No se puede eliminar el evento \"{$evento->titulo}\" porque tiene {$totalInscritos} ".
                ($totalInscritos === 1 ? 'inscripción confirmada.' : 'inscripciones confirmadas.').
                ' Los participantes deben cancelar su inscripción primero.'
            );
        }

        $evento->areas()->detach();
        $evento->ponentes()->detach();
        $evento->delete();
    }

    public function inscritos(Evento $evento): Collection
    {
        return $evento->inscripciones()
            ->with('user')
            ->conEstado(EstadoInscripcion::Confirmado)
            ->get()
            ->sortBy(fn (Inscripcion $inscripcion) => $inscripcion->user->nombre)
            ->values();
    }

    public function marcarAsistencia(Inscripcion $inscripcion, bool $asistio): Inscripcion
    {
        $inscripcion->update(['asistio' => $asistio]);

        return $inscripcion->fresh('user');
    }
}
