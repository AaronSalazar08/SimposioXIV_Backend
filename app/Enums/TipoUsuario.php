<?php

namespace App\Enums;

enum TipoUsuario: string
{
    case Admin = 'admin';
    case Staff = 'staff';
    case Participante = 'participante';
}
