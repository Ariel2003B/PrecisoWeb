<?php

use App\Http\Controllers\AuthLoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CaracteristicaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SancionesController;
use App\Http\Controllers\SimCardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VisitasController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'inicio'])->name('home.inicio');
Route::get('/privacidad', [HomeController::class, 'privacidad'])->name('home.privacidad');
Route::get('/plataformas', [HomeController::class, 'plataformas'])->name('home.plataformas');
Route::get('/servicios', [HomeController::class, 'servicios'])->name('home.servicios');
Route::get('/planes', [HomeController::class, 'planes'])->name('home.planes');
Route::get('/nosotros', [HomeController::class, 'nosotros'])->name('home.nosotros');
Route::get('/blogs', [HomeController::class, 'blog'])->name('home.blogs');
Route::get('/blogs/{id}', [HomeController::class, 'detailsBlog'])->name('blog.details');
//Route::post('/blogs', [HomeController::class, 'detailsBlog'])->name('blog.comment');
Route::post('/comentario/store', [ComentarioController::class, 'store'])->name('comentario.store');

Route::get('/incrementar-visitas', [VisitasController::class, 'incrementarVisitas']);
Route::get('/obtener-visitas', [VisitasController::class, 'obtenerVisitas']);



//routes for login process
// Mostrar el formulario de login
Route::get('/login', [AuthLoginController::class, 'showLoginForm'])->name('login.form');
// Procesar el login
Route::post('/login', [AuthLoginController::class, 'login'])->name('login');
// Cerrar sesion
Route::post('/logout', [AuthLoginController::class, 'logout'])->name('logout');


//rutas para el carrito
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/add', [CarritoController::class, 'addToCart'])->name('carrito.add');
Route::delete('/carrito/remove/{id}', [CarritoController::class, 'removeFromCart'])->name('carrito.remove');
Route::delete('/carrito/clear', [CarritoController::class, 'clearCart'])->name('carrito.clear');
Route::post('/carrito/update-quantity', [CarritoController::class, 'updateQuantity'])->name('carrito.updateQuantity');
//rute para pago
Route::post('/pago/inicio', [CarritoController::class, 'updateQuantity'])->name('pago.iniciar');


//rutas segun perfiles 
Route::middleware(['auth'])->group(function () {
    Route::middleware(['auth', 'role:USUARIOS'])->group(function () {
        Route::resource('usuario', UsuarioController::class);
    });
    Route::middleware(['auth', 'role:SIMCARDS'])->group(function () {
        Route::resource('simcards', SimCardController::class)->except(['show']);
        Route::post('/simcards/bulk-upload', [SimCardController::class, 'bulkUpload'])->name('simcards.bulkUpload');
        Route::get('/simcards/template', [SimCardController::class, 'downloadTemplate'])->name('simcards.template');
        Route::get('/simcards/update-wialon', [SimCardController::class, 'updateWialonPhones'])
            ->name('simcards.updateWialonPhones');
        Route::get('/simcards/updateSimCardFromWialon', [SimCardController::class, 'updateSimCardFromWialon'])
            ->name('simcards.updateSimCardFromWialon');
    });
    Route::middleware(['auth', 'role:PERFILES'])->group(function () {
        Route::resource('perfil', PerfilController::class);
        Route::post('/permisos', [PermisoController::class, 'store'])->name('permiso.store');


    });
    Route::resource('vehiculos', VehiculoController::class);
    Route::post('/simcards/fetch-wialon-data', [SimCardController::class, 'fetchWialonData'])->name('simcards.fetchWialonData');

    Route::middleware(['auth', 'role:SANCIONES'])->group(function () {
        Route::get('/sanciones/{parametro}', [SancionesController::class, 'index'])->name('sanciones.index');
        Route::post('/sanciones/cargarCSV', [SancionesController::class, 'cargarCSV'])->name('sanciones.cargarCSV'); // Cargar y procesar CSV
        Route::post('/sanciones/generarReporte', [SancionesController::class, 'generarReporte'])->name('sanciones.generarReporte'); // Generar reporte PDF
        Route::post('/sanciones/delete', [SancionesController::class, 'truncateTable'])->name('sanciones.truncate');
    });
    Route::middleware(['auth', 'role:PLANES'])->group(function () {
        Route::resource('plan', PlanController::class);
        Route::resource('caracteristica', CaracteristicaController::class);
        Route::get('/caracteristica/{id}/edit', [CaracteristicaController::class, 'edit'])->name('caracteristica.edit');
        Route::put('/caracteristica/{id}', [CaracteristicaController::class, 'update'])->name('caracteristica.update');

    });
    Route::middleware(['auth', 'role:BLOGS'])->group(function () {
        Route::resource('blog', BlogController::class);
    });

});



