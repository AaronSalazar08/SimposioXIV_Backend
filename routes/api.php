<?php

use App\Http\Controllers\Api\Admin\AreaAdminController;
use App\Http\Controllers\Api\Admin\AulaAdminController;
use App\Http\Controllers\Api\Admin\EventoAdminController;
use App\Http\Controllers\Api\Admin\HorarioAdminController;
use App\Http\Controllers\Api\Admin\PonenteAdminController;
use App\Http\Controllers\Api\Admin\UserAdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\InscripcionController;
use App\Http\Middleware\EsAdmin;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');

    Route::get('/inscripciones', [InscripcionController::class, 'index'])->name('inscripciones.index');
    Route::post('/inscripciones', [InscripcionController::class, 'store'])->name('inscripciones.store');
    Route::delete('/inscripciones/{inscripcion}', [InscripcionController::class, 'destroy'])->name('inscripciones.destroy');

    Route::middleware(EsAdmin::class)->prefix('admin')->group(function () {
        Route::get('/passwords/generar', [UserAdminController::class, 'generarPassword']);
        Route::post('/usuarios/{usuario}/enviar-correo', [UserAdminController::class, 'enviarCorreo']);
        Route::post('/correos/todos', [UserAdminController::class, 'enviarCorreoTodos']);
        Route::apiResource('usuarios', UserAdminController::class);

        Route::apiResource('eventos', EventoAdminController::class);
        Route::apiResource('horarios', HorarioAdminController::class);
        Route::apiResource('aulas', AulaAdminController::class);
        Route::apiResource('ponentes', PonenteAdminController::class);
        Route::apiResource('areas', AreaAdminController::class);
    });
});
