<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Collection;

class EventoAdminService
{
    public function listar(): Collection
    {
        return Evento::with(['horario.aula', 'ponente', 'areas'])
            ->join('horarios', 'horarios.id', '=', 'eventos.horario_id')
            ->orderBy('horarios.hora_inicio')
            ->select('eventos.*')
            ->get();
    }

    public function mostrar(Evento $evento): Evento
    {
        $evento->load(['horario.aula', 'ponente', 'areas']);

        return $evento;
    }

    public function crear(array $datos): Evento
    {
        $areaIds = $datos['area_ids'] ?? [];
        unset($datos['area_ids']);

        $evento = Evento::create($datos);

        if (! empty($areaIds)) {
            $evento->areas()->sync($areaIds);
        }

        $evento->load(['horario.aula', 'ponente', 'areas']);

        return $evento;
    }

    public function actualizar(Evento $evento, array $datos): Evento
    {
        $areaIds = array_key_exists('area_ids', $datos) ? $datos['area_ids'] : null;
        unset($datos['area_ids']);

        $evento->update($datos);

        if ($areaIds !== null) {
            $evento->areas()->sync($areaIds);
        }

        return $evento->fresh(['horario.aula', 'ponente', 'areas']);
    }

    public function eliminar(Evento $evento): void
    {
        $evento->areas()->detach();
        $evento->delete();
    }
}
