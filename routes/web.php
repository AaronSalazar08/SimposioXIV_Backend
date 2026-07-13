<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api-tester', function () {
    abort_unless(config('services.api_tester.enabled'), 404);

    return view('api-tester.index');
})->name('api-tester');
