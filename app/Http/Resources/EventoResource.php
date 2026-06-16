<?php

namespace App\Http\Resources;

use App\Enums\EstadoInscripcion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cuposDisponibles = max(0, $this->capacidad - $this->numero_inscritos);

        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo->value,
            'capacidad' => $this->capacidad,
            'numero_inscritos' => $this->numero_inscritos,
            'cupos_disponibles' => $cuposDisponibles,
            'tiene_capacidad_disponible' => $cuposDisponibles > 0,
            'esta_activo' => $this->esta_activo,
            'horario' => new HorarioResource($this->whenLoaded('horario')),
            'ponente' => new PonenteResource($this->whenLoaded('ponente')),
            'areas' => AreaResource::collection($this->whenLoaded('areas')),
            'usuario_inscrito' => $this->when(
                $request->user() !== null && $this->relationLoaded('inscripciones'),
                fn () => $this->inscripciones
                    ->contains(fn ($inscripcion) => $inscripcion->estado === EstadoInscripcion::Confirmado)
            ),
        ];
    }
}
