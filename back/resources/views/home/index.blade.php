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


    <div class="modal-navidad">
        <div class="modal-content-navidad">
            <div class="modal-header-navidad">
                <img src="{{ asset('img/Precisogps.png') }}" alt="Logo PrecisoGPS" class="logo-navidad">
                <button class="close-navidad" onclick="cerrarModal()">×</button>
            </div>
            <div class="modal-body-navidad">
                <img src="{{ asset('img/MariKari.jpg') }}" alt="Imagen navideña" class="imagen-navidad">
                <p class="mensaje-navidad">
                    <b>Todo detalle unido al amor y confianza hacen una gran navidad.</b>
                    Esta fecha nos permite agradecer su fidelidad y nos inspira a dar lo mejor de nosotros cada día. En
                    el 2025,
                    <b>ratificamos nuestro compromiso de mejora continua e innovación,</b> seguiremos acompañando su
                    ruta de
                    viaje y
                    esperamos que la carretera de su vida esté llena de logros, éxitos y felicidad.
                </p>
                <h4>FELIZ NAVIDAD</h4>
                <p>PrecisoGPS<br>Justo en el Punto</p>
            </div>
        </div>
    </div>
    <script>
        // Mostrar el modal automáticamente al cargar la página
        window.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('modalNavidad');
            modal.style.display = 'flex';
        });

        function mostrarModal() {
            const modal = document.querySelector('.modal-navidad');
            modal.style.display = 'flex';
        }

        function cerrarModal() {
            const modal = document.querySelector('.modal-navidad');
            modal.style.display = 'none';
        }

        // Cierra el modal al hacer clic fuera de él
        window.addEventListener('click', function(event) {
            const modal = document.querySelector('.modal-navidad');
            if (event.target === modal) {
                cerrarModal();
            }
        });
    </script>

@endsection
@section('jsCode', 'js/scripts.js')
