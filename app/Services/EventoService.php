<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Collection;

class EventoService
{
    /**
     * @param  array<string, mixed>  $filtros
     */
    public function listar(array $filtros, ?int $userId): Collection
    {
        $query = Evento::query()
            ->with([
                'horario.aula',
                'ponentes',
                'areas',
                'inscripciones' => fn ($q) => $q->where('user_id', $userId),
            ])
            ->activo();

        if (! empty($filtros['dia'])) {
            $query->porDia((int) $filtros['dia']);
        }

        if (! empty($filtros['tipo'])) {
            $query->porTipo((string) $filtros['tipo']);
        }

        if (! empty($filtros['area_id'])) {
            $query->porArea((int) $filtros['area_id']);
        }

        if (! empty($filtros['solo_disponibles'])) {
            $query->conCuposDisponibles();
        }

        return $query->ordenadoPorHorario()->get();
    }

    public function mostrar(Evento $evento, ?int $userId): Evento
    {
        $evento->load([
            'horario.aula',
            'ponentes',
            'areas',
            'inscripciones' => fn ($q) => $q->where('user_id', $userId),
        ]);

        return $evento;
    }
}
