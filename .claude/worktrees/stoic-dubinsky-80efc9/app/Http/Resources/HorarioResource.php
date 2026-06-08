<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HorarioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_dia' => $this->numero_dia,
            'hora_inicio' => $this->hora_inicio?->toIso8601String(),
            'hora_fin' => $this->hora_fin?->toIso8601String(),
            'aula' => new AulaResource($this->whenLoaded('aula')),
        ];
    }
}
