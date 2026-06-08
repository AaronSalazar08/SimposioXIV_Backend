<?php

namespace App\Services;

use App\Models\Area;
use Illuminate\Database\Eloquent\Collection;

class AreaService
{
    public function listar(): Collection
    {
        return Area::orderBy('nombre')->get();
    }

    public function crear(array $datos): Area
    {
        return Area::create($datos);
    }

    public function actualizar(Area $area, array $datos): Area
    {
        $area->update($datos);

        return $area->fresh();
    }

    public function eliminar(Area $area): void
    {
        $area->delete();
    }
}
