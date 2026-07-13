<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $baseDatosConectada = true;
        $errorBaseDatos = null;

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $baseDatosConectada = false;
            $errorBaseDatos = $e->getMessage();
        }

        return response()->json([
            'status' => $baseDatosConectada ? 'ok' : 'degraded',
            'app' => config('app.name'),
            'env' => app()->environment(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => [
                'connected' => $baseDatosConectada,
                'driver' => config('database.default'),
                'error' => $errorBaseDatos,
            ],
            'timestamp' => now()->toIso8601String(),
        ], $baseDatosConectada ? 200 : 503);
    }
}
