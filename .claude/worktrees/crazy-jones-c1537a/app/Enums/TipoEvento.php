<?php

namespace App\Enums;

enum TipoEvento: string
{
    case Apertura = 'apertura';
    case Clausura = 'clausura';
    case Taller = 'taller';
    case Charla = 'charla';
}
