<?php

namespace App\Models;

use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nombre', 'descripcion', 'color'])]
class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory;

    public function eventos(): BelongsToMany
    {
        return $this->belongsToMany(Evento::class, 'evento_area');
    }
}
