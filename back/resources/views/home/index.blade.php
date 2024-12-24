@extends('layout')
@section('Titulo', 'PrecisoGPS')

@section('content')
    <header class="masthead">
        <!-- <video autoplay muted loop class="masthead-video">
                                                <source src="/assets/img/AdobeStock_300771413.mov" type="video/mp4">
                                                Tu navegador no soporta la reproducción de video.
                                            </video> -->
        <video autoplay muted loop class="masthead-video">
            <source src="img/FondoNavidad.mp4" type="video/mp4">
            Tu navegador no soporta la reproducción de video.
        </video>
        <!-- Capa de superposición -->
        <div class="overlay"></div>
        <div class="container masthead-content">
            <p class="masthead-subheading">Supervisa tus vehículos en tiempo real y asegura un control total en
                cualquier lugar y momento.</p>
            <h1 class="masthead-heading">PROTECCIÓN Y MONITOREO CONSTANTE</h1>
            <a id="contador-visitas" class="btn btn-contador btn-xl text-uppercase" href="#">Cargando
                visitantes...</a>
        </div>

        <!-- <div id="contador-visitas" class="contador">
                                                
                                            </div> -->
    </header>

    <!-- Modal para mostrar el GIF -->
    <div id="modalNavidad" class="modal-navidad" onclick="cerrarModal(event)">
        <div class="modal-content-navidad" onclick="event.stopPropagation()">
            <!-- Cabecera del modal -->
            <div class="modal-header-navidad">
                <img src="{{ asset('img/Precisogps.png') }}" alt="Logo PrecisoGPS" class="logo-navidad">
                <button class="close-navidad" onclick="cerrarModal()">&times;</button>
            </div>
            <!-- Contenido del modal -->
            <div class="modal-body-navidad">
                <!-- GIF -->
                <img src="{{ asset('img/NavidadGift.gif') }}" alt="Feliz Navidad" class="imagen-navidad">
            </div>
        </div>
    </div>

    <script>
        // Función para abrir el modal
        function abrirModal() {
            const modal = document.getElementById('modalNavidad');
            modal.style.display = 'flex';
        }

        // Función para cerrar el modal
        function cerrarModal(event) {
            const modal = document.getElementById('modalNavidad');
            if (!event || event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Detectar tecla "Esc" para cerrar el modal
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                cerrarModal();
            }
        });
    </script>

@endsection
@section('jsCode', 'js/scripts.js')
