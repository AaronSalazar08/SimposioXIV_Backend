<?php

namespace App\Services;

use App\Models\Horario;
use Illuminate\Database\Eloquent\Collection;

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
        $horario->delete();
    }
}
