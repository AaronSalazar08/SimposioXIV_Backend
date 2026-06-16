<?php

namespace App\Exceptions\Inscripcion;

class CupoAgotadoException extends InscripcionBusinessException
{
    public function __construct()
    {
        parent::__construct('El evento ya no tiene cupos disponibles.');
    }

    public function field(): string
    {
        return 'evento_id';
    }
}
