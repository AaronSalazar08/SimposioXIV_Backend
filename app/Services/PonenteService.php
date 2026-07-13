<?php

namespace App\Services;

use App\Models\Ponente;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PonenteService
{
    public function listar(): Collection
    {
        return Ponente::orderBy('apellidos')->orderBy('nombre')->get();
    }

    public function crear(array $datos): Ponente
    {
        return Ponente::create($datos);
    }

    public function actualizar(Ponente $ponente, array $datos): Ponente
    {
        $ponente->update($datos);

        return $ponente->fresh();
    }

    public function eliminar(Ponente $ponente): void
    {
        $totalEventos = $ponente->eventos()->count();

        if ($totalEventos > 0) {
            $nombreCompleto = trim("{$ponente->nombre} {$ponente->apellidos}");

            throw new ConflictHttpException(
                "No se puede eliminar a {$nombreCompleto} porque está asignado a {$totalEventos} ".
                ($totalEventos === 1 ? 'evento.' : 'eventos.').
                ' Quitalo primero de esos eventos.'
            );
        }

        $ponente->delete();
    }
}
