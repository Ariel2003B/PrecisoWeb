<?php

use App\Http\Controllers\AuthLoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\SancionesController;
use App\Http\Controllers\SimCardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VisitasController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'inicio'])->name('home.inicio');
Route::get('/privacidad', [HomeController::class, 'privacidad'])->name('home.privacidad');
Route::get('/plataformas', [HomeController::class, 'plataformas'])->name('home.plataformas');
Route::get('/servicios', [HomeController::class, 'servicios'])->name('home.servicios');
Route::get('/planes', [HomeController::class, 'planes'])->name('home.planes');
Route::get('/nosotros', [HomeController::class, 'nosotros'])->name('home.nosotros');
Route::get('/incrementar-visitas', [VisitasController::class, 'incrementarVisitas']);
Route::get('/obtener-visitas', [VisitasController::class, 'obtenerVisitas']);

//routes for login process
// Mostrar el formulario de login
Route::get('/login', [AuthLoginController::class, 'showLoginForm'])->name('login.form');
// Procesar el login
Route::post('/login', [AuthLoginController::class, 'login'])->name('login');
// Cerrar sesion
Route::post('/logout', [AuthLoginController::class, 'logout'])->name('logout');

//rutas segun perfiles 
Route::middleware(['auth'])->group(function () {
    Route::middleware(['auth', 'role:USUARIOS'])->group(function () {
        Route::resource('usuario', UsuarioController::class);
    });
    Route::middleware(['auth', 'role:SIMCARDS'])->group(function () {
        Route::resource('simcards', SimCardController::class)->except(['show']);
        Route::post('/simcards/bulk-upload', [SimCardController::class, 'bulkUpload'])->name('simcards.bulkUpload');
        Route::get('/simcards/template', [SimCardController::class, 'downloadTemplate'])->name('simcards.template');
    });
    Route::middleware(['auth', 'role:PERFILES'])->group(function () {
        Route::resource('perfil', PerfilController::class);
    });
    Route::resource('vehiculos', VehiculoController::class);
    Route::post('/simcards/fetch-wialon-data', [SimCardController::class, 'fetchWialonData'])->name('simcards.fetchWialonData');
   
    Route::middleware(['auth', 'role:SANCIONES'])->group(function () {
        Route::get('/sanciones', [SancionesController::class, 'index'])->name('sanciones.index'); // Vista principal de sanciones
        Route::post('/sanciones/cargarCSV', [SancionesController::class, 'cargarCSV'])->name('sanciones.cargarCSV'); // Cargar y procesar CSV
        Route::post('/sanciones/generarReporte', [SancionesController::class, 'generarReporte'])->name('sanciones.generarReporte'); // Generar reporte PDF
    });
});

