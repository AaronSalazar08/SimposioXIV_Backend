<?php

namespace App\Models;

use App\Enums\EstadoInscripcion;
use Database\Factories\InscripcionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'evento_id', 'estado', 'enrolled_at', 'asistio'])]
class Inscripcion extends Model
{
    /** @use HasFactory<InscripcionFactory> */
    use HasFactory;

    protected $table = 'inscripciones';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado' => EstadoInscripcion::class,
            'enrolled_at' => 'datetime',
            'asistio' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public static function tieneSolapeDeHorario(int $userId, Horario $horario): bool
    {
        return self::query()
            ->where('inscripciones.user_id', $userId)
            ->where('inscripciones.estado', EstadoInscripcion::Confirmado->value)
            ->join('eventos', 'eventos.id', '=', 'inscripciones.evento_id')
            ->join('horarios', 'horarios.id', '=', 'eventos.horario_id')
            ->where('horarios.hora_inicio', '<', $horario->hora_fin)
            ->where('horarios.hora_fin', '>', $horario->hora_inicio)
            ->exists();
    }

    /**
     * @param  Builder<Inscripcion>  $query
     */
    public function scopeDelUsuario(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * @param  Builder<Inscripcion>  $query
     */
    public function scopeConEstado(Builder $query, EstadoInscripcion $estado): void
    {
        $query->where('estado', $estado);
    }
}
