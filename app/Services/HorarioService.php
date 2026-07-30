<?php

namespace App\Services;

use App\Models\Horario;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class HorarioService
{
    /**
     * Fecha real de cada día del simposio. hora_inicio/hora_fin se validan como
     * solo-hora (H:i) en el formulario del admin, así que hay que completar la
     * fecha manualmente o Carbon la rellena con la fecha en que se guardó el
     * registro, rompiendo el orden cronológico entre horarios.
     *
     * @var array<int, string>
     */
    private const FECHAS_POR_DIA = [
        1 => '2026-08-05',
        2 => '2026-08-06',
        3 => '2026-08-07',
    ];

    public function listar(): Collection
    {
        return Horario::with('aula')
            ->orderBy('numero_dia')
            ->orderBy('hora_inicio')
            ->get();
    }

    public function crear(array $datos): Horario
    {
        $horario = Horario::create($this->combinarFechaConHora($datos));
        $horario->load('aula');

        return $horario;
    }

    public function actualizar(Horario $horario, array $datos): Horario
    {
        $horario->update($this->combinarFechaConHora($datos, $horario));

        return $horario->fresh(['aula']);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function combinarFechaConHora(array $datos, ?Horario $horario = null): array
    {
        $dia = $datos['numero_dia'] ?? $horario?->numero_dia;
        $fecha = self::FECHAS_POR_DIA[$dia] ?? null;

        if ($fecha === null) {
            return $datos;
        }

        foreach (['hora_inicio', 'hora_fin'] as $campo) {
            if (isset($datos[$campo])) {
                $datos[$campo] = "{$fecha} {$datos[$campo]}:00";
            } elseif ($horario !== null && (int) $horario->numero_dia !== (int) $dia) {
                $datos[$campo] = $fecha.' '.$horario->{$campo}->format('H:i:s');
            }
        }

        return $datos;
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

    /**
     * Recalcula la fecha de hora_inicio/hora_fin de todos los horarios a partir de
     * su numero_dia, preservando la hora del día ya guardada. Repara horarios
     * creados antes del fix que quedaron con la fecha de creación/edición en vez
     * de la fecha real del simposio.
     *
     * @return array<int, array{horario: Horario, hora_inicio_antes: string, hora_fin_antes: string, hora_inicio_despues: string, hora_fin_despues: string}>
     */
    public function corregirFechasPorDia(bool $dryRun = false): array
    {
        $corregidos = [];

        foreach (Horario::all() as $horario) {
            $fecha = self::FECHAS_POR_DIA[$horario->numero_dia] ?? null;

            if ($fecha === null) {
                continue;
            }

            $horaInicioAntes = $horario->hora_inicio->format('Y-m-d H:i:s');
            $horaFinAntes = $horario->hora_fin->format('Y-m-d H:i:s');
            $horaInicioNueva = $fecha.' '.$horario->hora_inicio->format('H:i:s');
            $horaFinNueva = $fecha.' '.$horario->hora_fin->format('H:i:s');

            if ($horaInicioAntes === $horaInicioNueva && $horaFinAntes === $horaFinNueva) {
                continue;
            }

            $corregidos[] = [
                'horario' => $horario,
                'hora_inicio_antes' => $horaInicioAntes,
                'hora_fin_antes' => $horaFinAntes,
                'hora_inicio_despues' => $horaInicioNueva,
                'hora_fin_despues' => $horaFinNueva,
            ];

            if (! $dryRun) {
                $horario->update([
                    'hora_inicio' => $horaInicioNueva,
                    'hora_fin' => $horaFinNueva,
                ]);
            }
        }

        return $corregidos;
    }
}
