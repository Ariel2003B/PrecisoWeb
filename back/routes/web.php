<?php

use App\Http\Controllers\AplicacionController;
use App\Http\Controllers\AsignacionUnidadController;
use App\Http\Controllers\AuthLoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CaracteristicaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EquipoAccesorioController;
use App\Http\Controllers\GeocercaController;
use App\Http\Controllers\HojaTrabajoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MinutosCaidosController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\QrUnidadesController;
use App\Http\Controllers\ReporteProduccionController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\SancionesController;
use App\Http\Controllers\SimCardController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VisitasController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'inicio'])->name('home.inicio');
Route::get('/privacidad', [HomeController::class, 'privacidad'])->name('home.privacidad');
Route::get('/plataformas', [HomeController::class, 'plataformas'])->name('home.plataformas');
Route::get('/servicios', [HomeController::class, 'servicios'])->name('home.servicios');
Route::get('/tutoriales', [HomeController::class, 'planes'])->name('home.tutoriales');
Route::get('/planes', [HomeController::class, 'planes'])->name('home.planes');
Route::get('/nosotros', [HomeController::class, 'nosotros'])->name('home.nosotros');
Route::get('/blogs', [HomeController::class, 'blog'])->name('home.blogs');
Route::get('/blogs/{id}', [HomeController::class, 'detailsBlog'])->name('blog.details');
Route::get('/equiposacc', [HomeController::class, 'equipos'])->name('home.equipos');
//Route::post('/blogs', [HomeController::class, 'detailsBlog'])->name('blog.comment');
Route::post('/comentario/store', [ComentarioController::class, 'store'])->name('comentario.store');

Route::get('/incrementar-visitas', [VisitasController::class, 'incrementarVisitas']);
Route::get('/obtener-visitas', [VisitasController::class, 'obtenerVisitas']);


Route::get ('/aplicacion',[AplicacionController::class, 'index'])->name('aplicacion.index');

Route::resource('unidades', UnidadController::class);
Route::resource('rutasapp', RutaController::class);
Route::get('asignacion', [AsignacionUnidadController::class, 'index'])->name('asignacion.index');
Route::post('asignacion/asignar', [AsignacionUnidadController::class, 'asignar'])->name('asignacion.asignar');

//routes for login process
// Mostrar el formulario de login
Route::get('/login', [AuthLoginController::class, 'showLoginForm'])->name('login.form');
// Procesar el login
Route::post('/login', [AuthLoginController::class, 'login'])->name('login');
// Cerrar sesion
Route::post('/logout', [AuthLoginController::class, 'logout'])->name('logout');


Route::resource('equipos', EquipoAccesorioController::class);


//rutas para el carrito
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/add', [CarritoController::class, 'addToCart'])->name('carrito.add');
Route::delete('/carrito/remove/{id}', [CarritoController::class, 'removeFromCart'])->name('carrito.remove');
Route::delete('/carrito/clear', [CarritoController::class, 'clearCart'])->name('carrito.clear');
Route::post('/carrito/update-quantity', [CarritoController::class, 'updateQuantity'])->name('carrito.updateQuantity');
//rute para pago
Route::post('/pago/inicio', [CarritoController::class, 'updateQuantity'])->name('pago.iniciar');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

Route::get('/log-test', function () {
    Log::info('Escribiendo en el log desde /log-test');
    return 'Log generado';
});

//rutas segun perfiles 
Route::middleware(['auth'])->group(function () {
    Route::resource('empresa', EmpresaController::class);

    Route::get('/reportes', [ReporteProduccionController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/registrar/{id}', [ReporteProduccionController::class, 'create'])->name('reportes.create');
    Route::post('/reportes/guardar', [ReporteProduccionController::class, 'store'])->name('reportes.store');
    Route::middleware(['auth', 'role:GESTION DE USUARIOS'])->group(function () {
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
    Route::middleware(['auth', 'role:GESTION DE PERFILES'])->group(function () {
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
    Route::middleware(['auth', 'role:GESTION DE PLANES'])->group(function () {
        Route::resource('plan', PlanController::class);
        Route::resource('caracteristica', CaracteristicaController::class);
        Route::get('/caracteristica/{id}/edit', [CaracteristicaController::class, 'edit'])->name('caracteristica.edit');
        Route::put('/caracteristica/{id}', [CaracteristicaController::class, 'update'])->name('caracteristica.update');

    });
    Route::middleware(['auth', 'role:GESTION DE BLOGS'])->group(function () {
        Route::resource('blog', BlogController::class);
    });

    //geocerca
    Route::middleware(['auth', 'role:WIALON DATA'])->group(function () {
        Route::post('/geocercas/crear', [GeocercaController::class, 'crear'])->name('geocercas.crear');
        Route::get('/geocercas', [GeocercaController::class, 'index'])->name('geocercas.index');
        Route::post('/geocercas/depot', [GeocercaController::class, 'obtenerDepots'])->name('geocercas.obtenerDepots');
        Route::post('/geocercas/eliminar', [GeocercaController::class, 'eliminar'])->name('geocercas.eliminar');

    });
    Route::get('/rutas', [MinutosCaidosController::class, 'index'])->name('rutas.index');
    Route::get('/rutas/minutos-caidos', [MinutosCaidosController::class, 'actualizarTabla'])->name('rutas.actualizar');

    Route::get('/simcards/exportExcel', [SimCardController::class, 'generarReporteExcel'])->name('simcards.exportExcel');
    Route::get('/hoja-trabajo/{id}', [HojaTrabajoController::class, 'verHojaTrabajo'])->name('hoja.ver');
    //Route::get('/reporte-global', [ReporteProduccionController::class, 'verReporteGlobal'])->name('reporte.global');
    Route::post('/generar-reporte-global', [ReporteProduccionController::class, 'generarReporteGlobal'])->name('reporte.global');
    Route::get('/reporte/global/excel', [ReporteProduccionController::class, 'generarExcel'])->name('reporte.global.excel');
    Route::get('/reporte/global/pdf', [ReporteProduccionController::class, 'generarPDF'])->name('reporte.global.pdf');
    Route::get('/reporte-pdf-rango', [HojaTrabajoController::class, 'reportePorRango'])->name('reporte.pdf.rango');

}); 



