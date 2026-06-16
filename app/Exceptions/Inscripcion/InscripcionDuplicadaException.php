<?php

namespace App\Exceptions\Inscripcion;

class InscripcionDuplicadaException extends InscripcionBusinessException
{
    public function __construct()
    {
        parent::__construct('Ya estás inscrito en este evento.');
    }

    public function field(): string
    {
        return 'evento_id';
    }
}
