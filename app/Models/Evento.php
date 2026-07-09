<?php

namespace App\Models;

use App\Enums\TipoEvento;
use Database\Factories\EventoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['horario_id', 'titulo', 'descripcion', 'tipo', 'capacidad', 'esta_activo'])]
class Evento extends Model
{
    /** @use HasFactory<EventoFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo' => TipoEvento::class,
            'esta_activo' => 'boolean',
        ];
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    public function ponentes(): BelongsToMany
    {
        return $this->belongsToMany(Ponente::class, 'evento_ponente');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'evento_area');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'inscripciones')
            ->withPivot(['estado', 'enrolled_at']);
    }

    public function tieneCapacidadDisponible(): bool
    {
        return $this->numero_inscritos < $this->capacidad;
    }

    /**
     * @param  Builder<Evento>  $query
     */
    public function scopeActivo(Builder $query): void
    {
        $query->where('esta_activo', true);
    }

    /**
     * @param  Builder<Evento>  $query
     */
    public function scopeConCuposDisponibles(Builder $query): void
    {
        $query->whereColumn('numero_inscritos', '<', 'capacidad');
    }

    /**
     * @param  Builder<Evento>  $query
     */
    public function scopeOrdenadoPorHorario(Builder $query): void
    {
        $query->join('horarios', 'horarios.id', '=', 'eventos.horario_id')
            ->orderBy('horarios.hora_inicio')
            ->select('eventos.*');
    }

    /**
     * @param  Builder<Evento>  $query
     */
    public function scopePorDia(Builder $query, int $dia): void
    {
        $query->whereHas('horario', fn (Builder $q) => $q->where('numero_dia', $dia));
    }

    /**
     * @param  Builder<Evento>  $query
     */
    public function scopePorTipo(Builder $query, string $tipo): void
    {
        $query->where('tipo', $tipo);
    }

    /**
     * @param  Builder<Evento>  $query
     */
    public function scopePorArea(Builder $query, int $areaId): void
    {
        $query->whereHas('areas', fn (Builder $q) => $q->where('areas.id', $areaId));
    }
}
