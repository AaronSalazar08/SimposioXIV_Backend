<?php

namespace App\Console\Commands;

use App\Enums\EstadoInscripcion;
use App\Models\Evento;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('eventos:sync-cupos')]
#[Description('Sincroniza numero_inscritos con el conteo real de inscripciones confirmadas')]
class SyncEventoCuposCommand extends Command
{
    public function handle(): int
    {
        $actualizados = 0;

        Evento::query()->each(function (Evento $evento) use (&$actualizados): void {
            $conteoReal = $evento->inscripciones()
                ->where('estado', EstadoInscripcion::Confirmado)
                ->count();

            $anterior = $evento->numero_inscritos;

            if ($anterior !== $conteoReal) {
                $evento->forceFill(['numero_inscritos' => $conteoReal])->save();
                $actualizados++;
                $this->line("Evento #{$evento->id}: {$anterior} → {$conteoReal}");
            }
        });

        $this->info("Sincronización completada. {$actualizados} evento(s) actualizado(s).");

        return self::SUCCESS;
    }
}
