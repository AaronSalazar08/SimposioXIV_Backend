<?php

namespace App\Exceptions\Inscripcion;

class HorarioSolapadoException extends InscripcionBusinessException
{
    public function __construct()
    {
        parent::__construct('Ya tienes una inscripción activa que se solapa con este horario.');
    }

    public function field(): string
    {
        return 'evento_id';
    }
}
