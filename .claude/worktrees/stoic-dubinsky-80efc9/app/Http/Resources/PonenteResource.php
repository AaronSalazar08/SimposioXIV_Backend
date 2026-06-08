<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PonenteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'nombre_completo' => trim("{$this->nombre} {$this->apellidos}"),
            'educacion' => $this->educacion,
            'grado_academico' => $this->grado_academico,
            'descripcion' => $this->descripcion,
        ];
    }
}
