@extends('layout')
@section('Titulo', 'Planes')
@section('ActivarPlanes', 'active')
@section('content')
    <br>
    <br>
    <br>
    <br>
    <section class="page-section bg-light" id="portfolio">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Nuestros planes</h2>
                <h3 class="section-subheading text-muted">En PrecisoGPS, cuidamos lo que más valoras. Encuentra tu plan
                    ideal.</h3>
            </div>
            <div class="row">

                <div class="col-lg-6 col-sm-6 mb-4">
                    <!-- Plan GPS Pro -->
                    <div class="portfolio-item">
                        <a class="portfolio-link" data-bs-toggle="modal" href="#portfolioModal1">
                            <div class="portfolio-hover">
                                <div class="portfolio-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="{{asset('img/portfolio/precisoSatelital.jpg')}}" alt="GPS Pro" />
                        </a>
                        <div class="portfolio-caption">
                            <div class="portfolio-caption-heading">GPS Pro</div>
                            <div class="portfolio-caption-subheading text-muted">Beneficios Avanzados</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-6 mb-4">
                    <!-- Plan GPS Básico -->
                    <div class="portfolio-item">
                        <a class="portfolio-link" data-bs-toggle="modal" href="#portfolioModal2">
                            <div class="portfolio-hover">
                                <div class="portfolio-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="{{asset('img/portfolio/precisoSatelital2.png')}}" alt="GPS Básico" />
                        </a>
                        <div class="portfolio-caption">
                            <div class="portfolio-caption-heading">GPS Básico</div>
                            <div class="portfolio-caption-subheading text-muted">Características Esenciales</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal para GPS Pro -->
    <div class="portfolio-modal modal fade" id="portfolioModal1" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="close-modal" data-bs-dismiss="modal"><img src="{{asset('img/close-icon.svg')}}" alt="Close modal" />
                </div>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="modal-body text-center">
                                <!-- Título y subtítulo -->
                                <h2 class="text-uppercase">GPS Pro</h2>
                                <p class="item-intro text-muted">Beneficios avanzados para el control total de tu
                                    vehículo.</p>
                                <!-- Imagen con estilo llamativo -->
                                <div class="img-container">
                                    <img class="img-fluid d-block mx-auto rounded shadow"
                                        src="{{asset('img/portfolio/precisoSatelital.jpg')}}" alt="GPS Pro"
                                        style="width: 60%; border: 3px solid #ffc800;" />
                                </div>
                                <!-- Características principales -->
                                <div class="features mt-4">
                                    <h4 class="text-uppercase">Características Destacadas:</h4>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-shield-alt text-warning"></i> Protección
                                                    Anti-jammer</li>
                                                <li><i class="fas fa-bell text-warning"></i> Notificación de arrastre
                                                </li>
                                                <li><i class="fas fa-map-marker-alt text-warning"></i> Geocercas
                                                    personalizadas</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-mobile-alt text-warning"></i> Control remoto</li>
                                                <li><i class="fas fa-broadcast-tower text-warning"></i> Monitoreo 24/7
                                                </li>
                                                <li><i class="fas fa-car text-warning"></i> Bloqueo de motor</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- Precio y botón -->
                                <div class="mt-4">
                                    <h4 class="text-uppercase">Precio: <span class="text-warning">$379.91</span></h4>
                                    <p class="text-muted">Incluye dispositivo, instalación, y servicio por un año.</p>
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal"
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


    <!-- Modal para GPS Básico -->
    <div class="portfolio-modal modal fade" id="portfolioModal2" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="close-modal" data-bs-dismiss="modal"><img src="{{asset('img/close-icon.svg')}}" alt="Close modal" />
                </div>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="modal-body text-center">
                                <!-- Título y subtítulo -->
                                <h2 class="text-uppercase">GPS Básico</h2>
                                <p class="item-intro text-muted">Características esenciales para un rastreo confiable.
                                </p>
                                <!-- Imagen con estilo llamativo -->
                                <div class="img-container">
                                    <img class="img-fluid d-block mx-auto rounded shadow"
                                        src="{{asset('img/portfolio/precisoSatelital2.png')}}"   alt="GPS Básico"
                                        style="width: 60%; border: 3px solid #ffc800;" />
                                </div>
                                <!-- Características principales -->
                                <div class="features mt-4">
                                    <h4 class="text-uppercase">Características Destacadas:</h4>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-broadcast-tower text-warning"></i> Monitoreo 24/7
                                                </li>
                                                <li><i class="fas fa-map-marker-alt text-warning"></i> Ubicación precisa
                                                </li>
                                                <li><i class="fas fa-bell text-warning"></i> Alertas de
                                                    encendido/apagado</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-car text-warning"></i> Bloqueo de motor</li>
                                                <li><i class="fas fa-mobile-alt text-warning"></i> Geocercas</li>
                                                <li><i class="fas fa-share-alt text-warning"></i> Uso compartido</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- Precio y botón -->
                                <div class="mt-4">
                                    <h4 class="text-uppercase">Precio: <span class="text-warning">$230</span></h4>
                                    <p class="text-muted">Incluye dispositivo, instalación, y servicio por un año.</p>
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal"
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
@section('jsCode', 'js/scriptNavBar.js')
