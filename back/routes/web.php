<?php

use App\Http\Controllers\AuthLoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\SimCardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VisitasController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'inicio'])->name('home.inicio');
Route::get('/privacidad', [HomeController::class, 'privacidad'])->name('home.privacidad');
Route::get('/accesoCliente', [HomeController::class, 'accesoCliente'])->name('home.accesoCliente');
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
    Route::resource('perfil', PerfilController::class);
    Route::resource('usuario', UsuarioController::class);
    Route::resource('vehiculos', VehiculoController::class);
    Route::resource('simcards', SimCardController::class);
    Route::post('/simcards/bulk-upload', [SimCardController::class, 'bulkUpload'])->name('simcards.bulkUpload');

});