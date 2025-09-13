<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApiNimbusAppController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\HojaChoferController;
use App\Http\Controllers\HojaTrabajoController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Buscar (y crear si no existe) la hoja del día para la unidad
Route::get('/hoja-chofer/{id_unidad}', [HojaChoferController::class, 'buscarPorUnidad']);

// Actualizar las vueltas (producción) de la hoja de trabajo del día
Route::put('/hoja-chofer/{id_hoja}/produccion', [HojaChoferController::class, 'actualizarProduccion']);
Route::get('/hoja-trabajo/{id}', [HojaTrabajoController::class, 'verHojaTrabajoApi']);

Route::get('/hojas', [HojaTrabajoController::class, 'index']);
Route::put('/hojas/{id}', [HojaTrabajoController::class, 'update']);
Route::delete('/hojas/{id}', [HojaTrabajoController::class, 'destroy']);
Route::prefix('conductores')->group(function () {
    Route::get('/', [ConductorController::class, 'index']);
    Route::post('/', [ConductorController::class, 'store']);
    Route::get('/{id}', [ConductorController::class, 'show']);
    Route::put('/{id}', [ConductorController::class, 'update']);
    Route::delete('/{id}', [ConductorController::class, 'destroy']);
});
Route::post('/auth', [LoginController::class, 'auth']);
Route::get('/hojas-trabajo/{id}/generar-pdf', [HojaTrabajoController::class, 'generarPDF']);
Route::get('/hojas-trabajo/{id}/generar-pdfWeb', [HojaTrabajoController::class, 'generarPDFWeb']);
Route::get('/empresa/rutas', [RutaController::class, 'rutasPorEmpresa']);
Route::post('/data-inicial', [ApiNimbusAppController::class, 'getUnidadByPlaca']);
Route::post('/update/idwialon', [ApiNimbusAppController::class, 'updateIdWialon']);



Route::middleware('auth:sanctum')->group(function () {
    //Route::post('/auth', LoginController::class, 'Auth');
    Route::post('/hojas-trabajo', [HojaTrabajoController::class, 'store']);

    Route::get('/user', [LoginController::class, 'user']);

});

Route::post('/account/deletion-request', [AccountController::class, 'deletionRequest']);
