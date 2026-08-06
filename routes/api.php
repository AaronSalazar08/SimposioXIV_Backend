<?php

use App\Http\Controllers\Api\Admin\AreaAdminController;
use App\Http\Controllers\Api\Admin\AulaAdminController;
use App\Http\Controllers\Api\Admin\EventoAdminController;
use App\Http\Controllers\Api\Admin\HorarioAdminController;
use App\Http\Controllers\Api\Admin\PonenteAdminController;
use App\Http\Controllers\Api\Admin\UserAdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\InscripcionController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Middleware\EsAdmin;
use App\Http\Middleware\EsStaffOAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('health');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    Route::prefix('password')->group(function () {
        Route::post('/otp', [PasswordController::class, 'enviarOtp'])
            ->middleware('throttle:6,5')
            ->name('password.otp.enviar');
        Route::post('/otp/verificar', [PasswordController::class, 'verificarOtp'])
            ->middleware('throttle:10,1')
            ->name('password.otp.verificar');
        Route::put('/cambiar', [PasswordController::class, 'cambiarPassword'])
            ->name('password.cambiar');
    });

    Route::get('/eventos', [EventoController::class, 'index']);
    Route::get('/eventos/{evento}', [EventoController::class, 'show']);

    Route::get('/inscripciones', [InscripcionController::class, 'index'])->name('inscripciones.index');
    Route::post('/inscripciones', [InscripcionController::class, 'store'])->name('inscripciones.store');
    Route::delete('/inscripciones/{inscripcion}', [InscripcionController::class, 'destroy'])->name('inscripciones.destroy');

    Route::prefix('admin')->group(function () {
        Route::middleware(EsStaffOAdmin::class)->group(function () {
            Route::get('/eventos', [EventoAdminController::class, 'index']);
            Route::get('/eventos/{evento}', [EventoAdminController::class, 'show']);
            Route::get('/eventos/{evento}/inscritos', [EventoAdminController::class, 'inscritos']);
            Route::put('/eventos/{evento}/inscritos/{inscripcion}/asistencia', [EventoAdminController::class, 'actualizarAsistencia']);
        });

        Route::middleware(EsAdmin::class)->group(function () {
            Route::get('/passwords/generar', [UserAdminController::class, 'generarPassword']);
            Route::post('/usuarios/{usuario}/enviar-correo', [UserAdminController::class, 'enviarCorreo']);
            Route::post('/correos/todos', [UserAdminController::class, 'enviarCorreoTodos']);
            Route::apiResource('usuarios', UserAdminController::class);

            Route::post('/eventos', [EventoAdminController::class, 'store']);
            Route::put('/eventos/{evento}', [EventoAdminController::class, 'update']);
            Route::delete('/eventos/{evento}', [EventoAdminController::class, 'destroy']);

            Route::apiResource('horarios', HorarioAdminController::class);
            Route::apiResource('aulas', AulaAdminController::class);
            Route::apiResource('ponentes', PonenteAdminController::class);
            Route::apiResource('areas', AreaAdminController::class);
        });
    });
});
