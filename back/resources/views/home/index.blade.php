@extends('layout')
@section('Titulo', 'PrecisoGPS')
@section('ActivarInicio', 'active')
@section('content')
    <main class="main">
        <!-- Hero Section -->
        <section id="hero" class="hero section dark-background">

            <div id="hero-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
                <div class="carousel-item active">
                    <!-- <img src="assets/img/hero-carousel/hero-carousel-1.jpg" alt=""> -->
                    <img src="https://ccq.ec/wp-content/uploads/2023/06/230601_AM_MitosAcercaDelRastreoVehicular.jpg" alt="">
                  </div>
                {{-- <div class="carousel-item active">
                    <video autoplay muted loop class="masthead-video">
                        <source src="{{ asset('img/precisoimg/video2Preciso.mp4') }}" type="video/mp4">
                        Tu navegador no soporta la reproducción de video.
                    </video>
                    
                </div> --}}
            </div>

            <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
            </a>
            <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
                <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
            </a>

            <ol class="carousel-indicators"></ol>

            </div>

        </section>
    </main>
    {{-- <a id="contador-visitas" href="#">Cargando visitantes...</a> --}}

    <!-- Modal -->
    <div class="modal fade" id="tiktokModal" tabindex="-1" aria-labelledby="tiktokModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tiktokModalLabel">PrecisoGPS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <video id="tiktokVideo" controls autoplay muted loop style="width: 100%; height: auto;"
                        controlsList="nodownload" oncontextmenu="return false;">
                        <source src="{{ asset('img/precisoimg/lady.mp4') }}" type="video/mp4">
                        Tu navegador no soporta el video.
                    </video>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('tiktokModal');
            const videoElement = document.getElementById('tiktokVideo');
            const modal = new bootstrap.Modal(modalElement);

            // Mostrar el modal automáticamente
            modal.show();

            // Detener el video cuando se cierre el modal
            modalElement.addEventListener('hidden.bs.modal', function() {
                videoElement.pause(); // Pausa el video
                videoElement.currentTime = 0; // Reinicia el video a su inicio
            });
        });
    </script>


@endsection
