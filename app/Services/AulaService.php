<?php

namespace App\Services;

use App\Models\Aula;
use Illuminate\Database\Eloquent\Collection;

class AulaService
{
    public function listar(): Collection
    {
        return Aula::orderBy('edificio')->orderBy('numero')->get();
    }

    public function crear(array $datos): Aula
    {
        return Aula::create($datos);
    }

    public function actualizar(Aula $aula, array $datos): Aula
    {
        $aula->update($datos);

        return $aula->fresh();
    }

    public function eliminar(Aula $aula): void
    {
        $aula->delete();
    }
}
