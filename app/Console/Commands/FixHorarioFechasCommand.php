<?php

namespace App\Console\Commands;

use App\Services\HorarioService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('horarios:corregir-fechas {--dry-run : Solo mostrar qué cambiaría, sin guardar}')]
#[Description('Recalcula la fecha de hora_inicio/hora_fin de cada horario a partir de numero_dia, preservando la hora ya guardada')]
class FixHorarioFechasCommand extends Command
{
    public function handle(HorarioService $horarioService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $corregidos = $horarioService->corregirFechasPorDia($dryRun);

        foreach ($corregidos as $item) {
            $horario = $item['horario'];
            $this->line(
                "Horario #{$horario->id} (día {$horario->numero_dia}): ".
                "{$item['hora_inicio_antes']} → {$item['hora_inicio_despues']}, ".
                "{$item['hora_fin_antes']} → {$item['hora_fin_despues']}"
            );
        }

        $verbo = $dryRun ? 'a corregir' : 'corregido(s)';
        $this->info(count($corregidos)." horario(s) {$verbo}.");

        if ($dryRun && count($corregidos) > 0) {
            $this->comment('Ejecutá sin --dry-run para aplicar los cambios.');
        }

        return self::SUCCESS;
    }
}
