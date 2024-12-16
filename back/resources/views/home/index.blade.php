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
@endsection
@section('jsCode', 'js/scripts.js')
