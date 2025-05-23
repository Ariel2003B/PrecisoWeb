@extends('layout')
@section('Titulo', 'PrecisoGPS - Plataformas')
@section('ActivarPlataformas', 'active')
@section('content')

    <main class="main">
        <!-- Page Title -->
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Plataformas</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Plataformas</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <!-- Animación de Bienvenida -->
        <div id="welcomeAnimation" class="welcome-animation">
            <h1>¡Nos alegra verte de nuevo, {{ Auth::user()->NOMBRE . ' ' . Auth::user()->APELLIDO }}! 😊</h1>
            <p>¡Esperamos que tengas un día fantástico explorando nuestras plataformas! 🎉</p>
        </div>
        <section class="section" id="plataformas">
            <div class="container">
                <div class="row text-center">

                    {{-- FRASES ALEATOREAS CUANDO  SE LOGUEA EN PLATOFARMAS --}}
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'PRECISO BUS'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-bus fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">PRECISOBUS</h4>
                            <a class="btn btn-success" href="https://nimbus.wialon.com/login" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif
                    {{-- @if (Auth::user()->permisos->contains('DESCRIPCION', 'RASTREA TU VEHICULO'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-satellite-dish fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">RASTREA TU VEHICULO</h4>
                            <a class="btn btn-success" href="http://www.precisogps.online/" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif --}}
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'MINUTOS CAIDOS'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-clock fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">MINUTOS CAIDOS</h4>
                            <a class="btn btn-success" href="http://157.245.141.38:4020/login" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'MANTENIMIENTO VEHICULAR'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-wrench fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">MANTENIMIENTO VEHICULAR</h4>
                            <a class="btn btn-success" href="https://fleetrun.wialon.com/login" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'CAJA COMÚN'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-cash-register fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">CAJA COMÚN</h4>
                            <a class="btn btn-success" href="http://157.230.189.65:5030/login" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'CONTEO Y RECAUDO'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-solid fa-person fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">CONTEO Y RECAUDO</h4>
                            <a class="btn btn-success" href="{{ route('reportes.index') }}" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'SIMCARDS'))
                        <div class="col-md-4 text-center">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-sim-card fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">SIMCARDS</h4>
                            <button class="btn btn-success" onclick="mostrarModalClaro(event)">Opciones</button>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'SISTEMA CONTABLE'))
                        <div class="col-md-4 text-center">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-file-invoice fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">SISTEMA CONTABLE</h4>
                            <a class="btn btn-success" target="_blank" href="https://fws.com.ec">Visitar
                                pagina</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'REPLICAR GEOCERCAS'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-solid fa-circle-dot fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">REPLICAR GEOCERCAS</h4>
                            <a class="btn btn-success" href="{{ route('geocercas.index') }}" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'SECRETARIA DE MOVILIDAD'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-folder-open fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">SECRETARIA DE MOVILIDAD</h4>
                            <a class="btn btn-success" data-bs-toggle="modal" href="#modalSecretaria">Encuentra tu CIA</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'GOOGLE DRIVE'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-brands fa-google-drive fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">GOOGLE DRIVE</h4>
                            <a class="btn btn-success" target="_blank"
                                href="https://accounts.google.com/v3/signin/identifier?continue=https%3A%2F%2Fdrive.google.com%2Fdrive%2F%3Fdmr%3D1%26ec%3Dwgc-drive-hero-goto&followup=https%3A%2F%2Fdrive.google.com%2Fdrive%2F%3Fdmr%3D1%26ec%3Dwgc-drive-hero-goto&ifkv=AeZLP9-5DeLhxmOumIzRqjg75tnu7ARb-PJ4kJqAKXsKbT118fIevNTIhcCodd5k_VTr3SGo09e4gw&osid=1&passive=1209600&service=wise&flowName=GlifWebSignIn&flowEntry=ServiceLogin&dsh=S958401757%3A1735063661397253&ddm=1">Visitar
                                página</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'KARY LA PRECISA'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-solid fa-brands fa-whatsapp fa-stack-1x fa-inverse"></i>
                                {{-- <i class="fa-brands fa-square-whatsapp"></i> --}}
                            </span>
                            <h4 class="my-3">KARY LA PRECISA<h4>
                                    <a class="btn btn-success" href="https://apigatewaycenter.com/login"
                                        target="_blank">Visitar
                                        página</a>
                        </div>
                    @endif
                    {{-- @if (Auth::user()->permisos->contains('DESCRIPCION', 'E-DRIVERS'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-solid fa-car-side fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">E-DRIVERS</h4>
                            <a class="btn btn-success" href="http://159.223.161.160:3020/forms" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif --}}
                    {{-- @if (!Auth::check())
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-user-circle fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">ACCESO CLIENTES</h4>
                            <a class="btn btn-success" href="{{ route('login.form') }}">Iniciar sesion</a>
                        </div>
                    @else --}}

                    <!-- Modal para CLARO -->
                    <div id="modalClaro"
                        class="custom-modal position-absolute bg-white border rounded shadow p-3 text-center"
                        style="display: none;">
                        <p class="text-center mb-3 fw-bold">Selecciona una opción para SIMCARDS:</p>
                        <div>
                            <!-- Imagen con enlace -->
                            <a href="http://www.miclaro.com.ec/ivrdigital" target="_blank">
                                <img src="https://1000marcas.net/wp-content/uploads/2021/02/Claro-Logo-2004.png"
                                    alt="Imagen Claro 1" class="img-fluid mb-2" style="max-width: 90px;">
                            </a>
                            <p><a href="http://www.miclaro.com.ec/ivrdigital" target="_blank"
                                    class="text-primary">Reposicion de chips</a></p>
                            <!-- Otra Imagen con enlace -->
                            <a href="https://miclaro.com.ec/pagatufactura/web/index.php/llena/numero" target="_blank">
                                <img src="https://1000marcas.net/wp-content/uploads/2021/02/Claro-Logo-2004.png"
                                    alt="Imagen Claro 2" class="img-fluid mb-2" style="max-width: 90px;">
                            </a>
                            <p><a href="https://miclaro.com.ec/pagatufactura/web/index.php/llena/numero" target="_blank"
                                    class="text-primary">Factuacion CLARO</a></p>
                            @if (Auth::user()->permisos->contains('DESCRIPCION', 'SIMCARDS'))
                                <a href="{{ route('simcards.index') }}" target="_blank">
                                    <img src="{{ asset('img/precisoimg/logoPreciso.jpg') }}" alt="Imagen Claro 2"
                                        class="img-fluid mb-2" style="max-width: 90px;">
                                </a>
                                <p><a href="{{ route('simcards.index') }}" target="_blank" class="text-primary">Gestionar
                                        Simcards</a></p>
                            @endif
                        </div>
                    </div>

                    <!-- Sección WIALON -->
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'WIALON'))
                        <div class="col-md-4 text-center">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-solid fa-location-crosshairs fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">WIALON</h4>
                            <button class="btn btn-success" onclick="mostrarModalWialon(event)">Opciones</button>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'DIGITAL OCEAN'))
                        <div class="col-md-4 text-center">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-cloud fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">DIGITAL OCEAN</h4>
                            <a class="btn btn-success" target="_blank"
                                href="https://cloud.digitalocean.com/login">Visitar
                                pagina</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'GODADDY'))
                        <div class="col-md-4 text-center">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-computer fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">GODADDY</h4>
                            <a class="btn btn-success" target="_blank"
                                href="https://sso.godaddy.com/?realm=idp&app=cart&path=%2Fcheckoutapi%2Fv1%2Fredirects%2Flogin">Visitar
                                pagina</a>
                        </div>
                    @endif

                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'GESTION DE USUARIOS'))
                        <div class="col-md-4 text-center">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-users fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">GESTION DE USUARIOS</h4>
                            <a class="btn btn-success" href="{{ route('usuario.index') }}">Visitar
                                pagina</a>
                        </div>
                    @endif
                    {{-- @if (Auth::user()->permisos->contains('DESCRIPCION', 'GESTION DE PERFILES'))
                        <div class="col-md-4 text-center">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-lock fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">GESTION DE PERFILES</h4>
                            <a class="btn btn-success" href="{{ route('perfil.index') }}">Visitar
                                pagina</a>
                        </div>
                    @endif --}}
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'GESTION DE EMPRESAS'))
                        <div class="col-md-4 text-center">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-building fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">GESTION DE EMPRESAS</h4>
                            <a class="btn btn-success" href="{{ route('empresa.index') }}">Visitar
                                pagina</a>
                        </div>
                    @endif
                    {{-- @if (Auth::user()->permisos->contains('DESCRIPCION', 'SANCIONES'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-solid fa-money-bill fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">SANCIONES</h4>
                            <a class="btn btn-success" href="{{ route('sanciones.index', ['parametro' => 'S-N']) }}"
                                target="_blank">Visitar
                                página</a>
                        </div>
                    @endif --}}
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'GESTION DE PLANES'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-solid fa-money-bill-wave fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">GESTION DE PLANES</h4>
                            <a class="btn btn-success" href="{{ route('plan.index') }}" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'GESTION DE BLOGS'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-solid fa-blog fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">GESTION DE BLOGS</h4>
                            <a class="btn btn-success" href="{{ route('blog.index') }}" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'GESTION DE EQUIPOS Y ACCESORIOS'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-solid fa-money-bill-wave fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">GESTION DE EQUIPOS Y ACCESORIOS</h4>
                            <a class="btn btn-success" href="{{ route('equipos.index') }}" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif

                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'APLICATIVO MOVIL'))
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fa-solid fa-mobile fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">APLICATIVO MOVIL</h4>
                            <a class="btn btn-success" href="{{ route('aplicacion.index') }}" target="_blank">Visitar
                                página</a>
                        </div>
                    @endif

                    {{-- <i class="fa-solid fa-blog"></i> --}}

                    <!-- Modal para WIALON -->
                    <div id="modalWialon"
                        class="custom-modal position-absolute bg-white border rounded shadow p-3 text-center"
                        style="display: none;">
                        <p class="text-center mb-3 fw-bold">Selecciona una opción para WIALON:</p>
                        <div>
                            <!-- Imagen con enlace -->
                            <a href="https://cms.wialon.us" target="_blank">
                                <img src="https://help.wialon.com/download/attachments/7460006/wialonhostingen?version=3&modificationDate=1628841371129&api=v2"
                                    alt="Imagen WIALON 1" class="img-fluid mb-2" style="max-width: 80px;">
                            </a>
                            <p><a href="https://cms.wialon.us" target="_blank" class="text-primary">CMS WIALON</a>
                            </p>
                            <!-- Otra Imagen con enlace -->
                            <a href="https://my.wialon.com/es/login" target="_blank">
                                <img src="https://help.wialon.com/download/attachments/7460006/wialonhostingen?version=3&modificationDate=1628841371129&api=v2"
                                    alt="Imagen WIALON 2" class="img-fluid mb-2" style="max-width: 80px;">
                            </a>
                            <p><a href="https://my.wialon.com/es/login" target="_blank" class="text-primary">Pagos
                                    WIALON</a></p>
                        </div>
                    </div>
                </div>
                <style>
                    /* Estilos de la animación de bienvenida */
                    /* Estilos de la animación de bienvenida */
                    .welcome-animation {
                        position: fixed;
                        top: 20%;
                        left: 50%;
                        transform: translateX(-50%);
                        background-color: #fff;
                        padding: 30px;
                        border-radius: 20px;
                        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                        text-align: center;
                        z-index: 9999;
                        opacity: 0;
                        /* Inicia oculto */
                        visibility: hidden;
                        /* Inicia invisible */
                        transition: opacity 0.5s ease;
                        /* Transición suave al aparecer */
                    }



                    @keyframes fadeIn {
                        0% {
                            opacity: 0;
                            transform: translateY(-30px);
                        }

                        100% {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }

                    @keyframes fadeOut {
                        0% {
                            opacity: 1;
                        }

                        100% {
                            opacity: 0;
                        }
                    }
                </style>
                <script>
                    // Almacena el modal actualmente abierto
                    let modalAbierto = null;

                    // Función para cerrar todos los modales
                    function cerrarTodosLosModales() {
                        const modales = document.querySelectorAll('.custom-modal');
                        modales.forEach((modal) => {
                            modal.style.display = 'none';
                        });
                        modalAbierto = null; // Resetea la referencia del modal abierto
                    }

                    // Mostrar un modal específico
                    function mostrarModal(event, modalId, offsetTop) {
                        const modal = document.getElementById(modalId);

                        // Si el modal ya está abierto, cierra todos los modales
                        if (modalAbierto === modal) {
                            cerrarTodosLosModales();
                            return;
                        }

                        cerrarTodosLosModales(); // Cierra cualquier modal abierto

                        const button = event.target;

                        // Obtiene la posición del botón
                        const rect = button.getBoundingClientRect();
                        const offsetX = window.pageXOffset || document.documentElement.scrollLeft;
                        const offsetY = window.pageYOffset || document.documentElement.scrollTop;

                        // Posiciona el modal cerca del botón
                        modal.style.top = `${rect.top + offsetY - modal.offsetHeight + offsetTop}px`;
                        modal.style.left = `${rect.left + offsetX}px`;

                        // Muestra el modal
                        modal.style.display = 'block';
                        modalAbierto = modal; // Actualiza la referencia al modal abierto
                    }

                    // Detecta clic fuera de cualquier modal para cerrarlo
                    document.addEventListener('click', (event) => {
                        if (modalAbierto && !modalAbierto.contains(event.target) && !event.target.closest('.btn-success')) {
                            cerrarTodosLosModales();
                        }
                    });

                    // Detecta tecla "Esc" para cerrar cualquier modal
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            cerrarTodosLosModales();
                        }
                    });

                    // Función para mostrar el modal de Claro
                    function mostrarModalClaro(event) {
                        mostrarModal(event, 'modalClaro', -480);
                    }

                    // Función para mostrar el modal de Wialon
                    function mostrarModalWialon(event) {
                        mostrarModal(event, 'modalWialon', -420);
                    }
                </script>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const animationElement = document.getElementById('welcomeAnimation');

                        // Mostrar la animación correctamente al cargar la página
                        setTimeout(() => {
                            animationElement.style.visibility = 'visible';
                            animationElement.style.opacity = '1';
                        }, 100); // Pequeño retraso para asegurar la carga completa

                        // Desaparecer la animación después de 4 segundos
                        setTimeout(() => {
                            animationElement.style.opacity = '0';
                            setTimeout(() => {
                                animationElement.style.display = 'none';
                            }, 250); // Esperar a que termine la animación antes de ocultar el elemento
                        }, 1500);
                    });
                </script>
            </div>
        </section>
    </main>
    <div class="portfolio-modal modal fade modal-secretaria" id="modalSecretaria" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Botón para cerrar -->
                <div class="close-modal" data-bs-dismiss="modal">
                    <img src="{{ asset('img/close-icon.svg') }}" alt="Close modal" />
                </div>
                <!-- Contenido del modal -->
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="modal-body text-center">
                                <!-- Título -->
                                <h2 class="text-uppercase mb-3">Secretaría de Movilidad</h2>
                                <p class="text-muted mb-4">Lista de operadoras. Usa el filtro para encontrar
                                    fácilmente.
                                </p>
                                <!-- Campo de filtro -->
                                <input id="filtroModal" type="text" class="form-control mb-3"
                                    placeholder="Filtrar operadoras...">
                                <!-- Lista -->
                                <ul id="listaOperadoras" class="list-group text-start">
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EmWsJmG8EEFPquLbG0TbYrIBf-wlhylDidrO4fv8Nudafg?email=cia.metropoli%40gmail.com&e=0APeRY">
                                            Transmetropoli S.A.
                                        </a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/ElJtYx5sbLJJoKxyUcNBRrcBeQA5P3yXvSvBrjDSSRAKPg">
                                            Trans Sirena Express S.A.
                                        </a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/El7k5IVxJg9DqLpk4AUkaXkBFBQymvVjYsGhWWI4ofiivA?email=transperifericosb%40gmail.com&e=vBb7M4">
                                            Transperiféricos S.A.
                                        </a>
                                    </li>
                                    <li class="list-group-item">

                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EidP6bxW9BJKihOYsxVWWNIBftqzJDn0LYeFxCEo4VMFLg?email=intra31express%40gmail.com&e=ek2IIz">Intraexpress
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">

                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EllmmQmeM9xBv5R18R044xMBbZV1-WFnYWanpJ0pZyNtvg?email=operadora_quitumbe%40hotmail.com&e=00qvv5">Quitumbe
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/Ety5AbNLp7xIn0nKQ3G9NOwB0YVG3BXemX3nr6cZeoOsjg?email=tstransporsel%40hotmail.com&e=4jO9Bc">Transporsel
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EoQ17-OVtQxFvA8VYFoHWGIB-2jQwQojjh_YIS2XxCPD8A?email=kinaraexpress%40hotmail.com&e=gtKHm5">Kinara
                                            Express S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EiIwcao6BPlEjsYR0FEBWEABiKZmd0A0A-A9zspBvVt3aw?email=ciaruvitransa2017%40hotmail.com&e=B03UfB">Rutvitransa
                                            S.A</a>
                                    </li>


                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/ErOGhhjM_LBEsdAMGaH0MkQBg0eR0K-CLyi6GxFnsptbLw?email=transfloresta2%40gmail.com&e=r8JfWG">Transfloresta
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/Eix4q5kNVZ1Nn_zy4onKZFYBoc-Hnj1X535SyS5yBjtR0Q?email=urbanquito2017%40gmail.com&e=n9YNNw">UrbanQuito
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/Eimynka85wBPqjjUbFp6m14BSdHPe9v72DI1PHeoGo4YIw?email=stalin.yepez%40hotmail.com&e=jpXkzU">Nacional
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EotoF48EFdtNmyieE73Hqp4BFnQjj5AdEZdOJp44-FIECg?email=semgyllfor%40yahoo.com&e=2HeipY">Semgyllfor
                                            S.A.</a>
                                    </li>
                                </ul>
                                <!-- Botón de cierre -->
                                <div class="mt-4">
                                    <button class="btn btn-danger btn-xl text-uppercase" data-bs-dismiss="modal"
                                        type="button">
                                        <i class="fas fa-xmark me-1"></i> Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
