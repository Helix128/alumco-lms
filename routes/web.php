<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Rutas protegidas del dashboard
    Route::get('/', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');
});