<?php

namespace App\Services;

use App\Models\Area;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
        $totalEventos = $area->eventos()->count();

        if ($totalEventos > 0) {
            throw new ConflictHttpException(
                "No se puede eliminar el área \"{$area->nombre}\" porque está asignada a {$totalEventos} ".
                ($totalEventos === 1 ? 'evento.' : 'eventos.').
                ' Quitala primero de esos eventos.'
            );
        }

        $area->delete();
    }
}
