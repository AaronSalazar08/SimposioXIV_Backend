<?php

namespace App\Services;

use App\Models\Horario;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class HorarioService
{
    public function listar(): Collection
    {
        return Horario::with('aula')
            ->orderBy('numero_dia')
            ->orderBy('hora_inicio')
            ->get();
    }

    public function crear(array $datos): Horario
    {
        $horario = Horario::create($datos);
        $horario->load('aula');

        return $horario;
    }

    public function actualizar(Horario $horario, array $datos): Horario
    {
        $horario->update($datos);

        return $horario->fresh(['aula']);
    }

    public function eliminar(Horario $horario): void
    {
        $totalEventos = $horario->eventos()->count();

        if ($totalEventos > 0) {
            throw new ConflictHttpException(
                "No se puede eliminar este horario porque tiene {$totalEventos} ".
                ($totalEventos === 1 ? 'evento asignado.' : 'eventos asignados.').
                ' Eliminá o reasigná primero esos eventos.'
            );
        }

        $horario->delete();
    }
}
