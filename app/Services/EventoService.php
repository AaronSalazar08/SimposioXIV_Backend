<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Collection;

class EventoService
{
    public function listar(array $filtros, ?int $userId): Collection
    {
        $query = Evento::query()
            ->with([
                'horario.aula',
                'ponente',
                'areas',
                'inscripciones' => fn ($q) => $q->where('user_id', $userId),
            ])
            ->where('esta_activo', true);

        if (! empty($filtros['dia'])) {
            $query->whereHas('horario', fn ($q) => $q->where('numero_dia', (int) $filtros['dia']));
        }

        if (! empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (! empty($filtros['area_id'])) {
            $query->whereHas('areas', fn ($q) => $q->where('areas.id', (int) $filtros['area_id']));
        }

        if (! empty($filtros['solo_disponibles'])) {
            $query->whereColumn('numero_inscritos', '<', 'capacidad');
        }

        return $query
            ->join('horarios', 'horarios.id', '=', 'eventos.horario_id')
            ->orderBy('horarios.hora_inicio')
            ->select('eventos.*')
            ->get();
    }

    public function mostrar(Evento $evento, ?int $userId): Evento
    {
        $evento->load([
            'horario.aula',
            'ponente',
            'areas',
            'inscripciones' => fn ($q) => $q->where('user_id', $userId),
        ]);

        return $evento;
    }
}
