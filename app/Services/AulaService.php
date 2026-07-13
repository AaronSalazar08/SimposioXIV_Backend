<?php

namespace App\Services;

use App\Models\Aula;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
        $totalHorarios = $aula->horarios()->count();

        if ($totalHorarios > 0) {
            throw new ConflictHttpException(
                "No se puede eliminar el aula \"{$aula->numero}\" porque tiene {$totalHorarios} ".
                ($totalHorarios === 1 ? 'horario asignado.' : 'horarios asignados.').
                ' Eliminá o reasigná primero esos horarios.'
            );
        }

        $aula->delete();
    }
}
