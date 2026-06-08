<?php

namespace App\Models;

use Database\Factories\AulaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['numero', 'edificio', 'capacidad'])]
class Aula extends Model
{
    /** @use HasFactory<AulaFactory> */
    use HasFactory;

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }
}
