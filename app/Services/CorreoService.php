<?php

namespace App\Services;

use App\Enums\TipoUsuario;
use App\Mail\BienvenidaSimposio;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CorreoService
{
    public function __construct(private readonly UserService $userService) {}

    /**
     * Genera una nueva contraseña, actualiza el usuario y envía el correo de bienvenida.
     */
    public function enviarBienvenida(User $user): void
    {
        $passwordPlano = $this->userService->generarPassword();

        $user->update(['password' => $passwordPlano]);
        $user->refresh();

        Mail::to($user->email)->send(new BienvenidaSimposio($user, $passwordPlano));
    }

    /**
     * Envía correo de bienvenida a todos los participantes (excluye admins).
     *
     * @return array{ enviados: int, fallidos: int }
     */
    public function enviarBienvenidaTodos(): array
    {
        $usuarios = User::where('tipo_usuario', TipoUsuario::Participante->value)->get();

        $enviados = 0;
        $fallidos = 0;

        foreach ($usuarios as $usuario) {
            try {
                $this->enviarBienvenida($usuario);
                $enviados++;
            } catch (\Exception) {
                $fallidos++;
            }
        }

        return ['enviados' => $enviados, 'fallidos' => $fallidos];
    }
}
