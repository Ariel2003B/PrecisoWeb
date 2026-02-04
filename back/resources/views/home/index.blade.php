@extends('layout')
@section('Titulo', 'PrecisoGPS')
@section('ActivarInicio', 'active')
@section('content')
    <script src="https://cdn.pulse.is/livechat/loader.js" data-live-chat-id="68dda155e788d993f90f4455"  async></script>
    <main class="main">
        <!-- Hero Section -->
        <section id="hero" class="hero section dark-background">

            <div id="hero-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">

                <div class="carousel-item active">
                    <img src="{{ asset('img/precisoimg/carousel-2.jpg') }}" alt="">
                    <div class="container">
                        <h3 class="visitor-counter"><b><span class="contador-visitas">Cargando...</span></b></h3>
                    </div>
                </div><!-- End Carousel Item -->

                <div class="carousel-item">
                    <img src="{{ asset('img/precisoimg/carousel-3.jpg') }}" alt="">
                    <div class="container">
                        <h3 class="visitor-counter"><b><span class="contador-visitas">Cargando...</span></b></h3>
                    </div>
                </div><!-- End Carousel Item -->
                <div class="carousel-item">
                    <img src="{{ asset('img/precisoimg/carousel-5.jpg') }}" alt="">
                    <div class="container">
                        <h3 class="visitor-counter"><b><span class="contador-visitas">Cargando...</span></b></h3>
                    </div>
                </div><!-- End Carousel Item -->


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
                        <source src="{{ asset('img/precisoimg/publiPujili.mp4') }}" type="video/mp4">
                        Tu navegador no soporta el video.
                    </video>
                </div>
                {{-- <div class="modal-body text-center">
                    <img src="{{ asset('img/precisoimg/blackFriday.jpg') }}" alt="PrecisoGPS" style="width: 100%; height: auto;"
                        class="img-fluid">
                </div> --}}

            </div>
        </div>
    </div>
    <a href="https://wa.me/593991933924?text=Hola%2C%20me%20interesa%20asesoramiento%20sobre%20el%20rastreo%20vehicular.%20¿Podrías%20darme%20más%20información%3F"
        target="_blank" class="whatsapp-float">
        <span class="whatsapp-float-text">¡Comunícate ya!</span>
        <i class="bi bi-whatsapp"></i>

    </a>



    <a href="http://www.precisogps.online/" target="_blank" class="custom-float">
        <span class="custom-float-text">Rastrea tu vehículo</span>
        <div class="custom-float-circle">
            <img src="https://precisogps.com/img/precisoimg/logoPreciso.jpg" alt="Logo" class="custom-float-img">
        </div>
    </a>
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
