<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PbgController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas API para el Dashboard PBG (Producto Bruto Geográfico)
| Solo incluye las APIs que realmente se están utilizando
|
*/

// APIs PBG en uso
Route::prefix('pbg')->group(function () {
    Route::get('/charts', [PbgController::class, 'charts'])->name('pbg.charts');
    Route::get('/latest', [PbgController::class, 'latest'])->name('pbg.latest');
    Route::get('/years', [PbgController::class, 'getYears'])->name('pbg.years');
    Route::get('/sectors', [PbgController::class, 'getSectors'])->name('pbg.sectors');
    Route::get('/sector/{codigo}', [PbgController::class, 'bySector'])->name('pbg.bySector');
});