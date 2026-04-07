<?php

namespace App\Models;

use Database\Factories\HorarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['evento_id', 'aula_id', 'numero_dia', 'hora_inicio', 'hora_fin'])]
class Horario extends Model
{
    /** @use HasFactory<HorarioFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hora_inicio' => 'datetime',
            'hora_fin' => 'datetime',
        ];
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class);
    }
}
