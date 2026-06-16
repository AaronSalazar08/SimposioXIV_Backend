<?php

namespace App\Exceptions\Inscripcion;

class EventoInactivoException extends InscripcionBusinessException
{
    public function __construct()
    {
        parent::__construct('El evento no está activo.');
    }

    public function field(): string
    {
        return 'evento_id';
    }
}
