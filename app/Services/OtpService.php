<?php

namespace App\Services;

use App\Mail\CodigoOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    private const OTP_TTL = 120;       // 2 minutos

    private const VERIFIED_TTL = 300;  // 5 minutos para completar el cambio

    public function enviar(User $user): void
    {
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put($this->otpKey($user->id), $codigo, self::OTP_TTL);
        Mail::to($user->email)->send(new CodigoOtpMail($user, $codigo));
    }

    public function verificar(User $user, string $codigo): bool
    {
        $stored = Cache::get($this->otpKey($user->id));

        if ($stored === null || $stored !== $codigo) {
            return false;
        }

        Cache::forget($this->otpKey($user->id));
        Cache::put($this->verifiedKey($user->id), true, self::VERIFIED_TTL);

        return true;
    }

    public function estaVerificado(User $user): bool
    {
        return (bool) Cache::get($this->verifiedKey($user->id));
    }

    public function limpiarVerificacion(User $user): void
    {
        Cache::forget($this->otpKey($user->id));
        Cache::forget($this->verifiedKey($user->id));
    }

    private function otpKey(int $userId): string
    {
        return "otp:password:{$userId}";
    }

    private function verifiedKey(int $userId): string
    {
        return "otp:password:verified:{$userId}";
    }
}
