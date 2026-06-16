<?php

namespace App\Policies;

use App\Models\Inscripcion;
use App\Models\User;

class InscripcionPolicy
{
    public function delete(User $user, Inscripcion $inscripcion): bool
    {
        return $user->id === $inscripcion->user_id;
    }
}
