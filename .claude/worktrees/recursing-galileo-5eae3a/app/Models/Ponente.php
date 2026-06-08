<?php

namespace App\Models;

use Database\Factories\PonentesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'apellidos', 'educacion', 'grado_academico', 'descripcion'])]
class Ponente extends Model
{
    /** @use HasFactory<PonentesFactory> */
    use HasFactory;

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }
}
