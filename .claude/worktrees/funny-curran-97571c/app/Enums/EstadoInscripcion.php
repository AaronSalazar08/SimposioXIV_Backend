<?php

namespace App\Enums;

enum EstadoInscripcion: string
{
    case Pendiente = 'pendiente';
    case Confirmado = 'confirmado';
    case Cancelado = 'cancelado';
}
