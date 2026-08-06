<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InscripcionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'evento_id' => $this->evento_id,
            'estado' => $this->estado->value,
            'enrolled_at' => $this->enrolled_at?->toIso8601String(),
            'asistio' => $this->asistio,
            'evento' => new EventoResource($this->whenLoaded('evento')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
