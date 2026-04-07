<?php

namespace App\Models;

use App\Enums\TipoEvento;
use Database\Factories\EventoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['titulo', 'descripcion', 'tipo', 'capacidad', 'numero_inscritos', 'esta_activo'])]
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

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
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
}
