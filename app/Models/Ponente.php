<?php

namespace App\Models;

use Database\Factories\PonentesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nombre', 'apellidos', 'educacion', 'grado_academico', 'descripcion'])]
class Ponente extends Model
{
    /** @use HasFactory<PonentesFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return PonentesFactory::new();
    }

    public function eventos(): BelongsToMany
    {
        return $this->belongsToMany(Evento::class, 'evento_ponente');
    }
}
