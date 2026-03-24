<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReporteController;

// Tu ruta actual
Route::get('/', [ReporteController::class , 'index'])->name('reportes.index');

// NUEVA RUTA PARA EXPORTAR
Route::get('/exportar', [ReporteController::class , 'exportar'])->name('reportes.exportar');