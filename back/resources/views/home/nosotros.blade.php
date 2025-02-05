@extends('layout')
@section('Titulo', 'PrecisoGPS - Nosotros')
@section('ActivarNosotros', 'active')
@section('content')
    <main class="main">
        <!-- Page Title -->
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Nosotros</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{route('home.inicio')}}">Inicio</a></li>
                        <li class="current">Nosotros</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <!-- About Section -->
        <!-- About Section -->
        <section id="about" class="about section">

            <div class="container">
                <div class="row position-relative">

                    <div class="col-lg-7 about-img" data-aos="zoom-out" data-aos-delay="200"><img
                            src="{{ asset('img/about.jpg') }}" alt="Misión"></div>

                    <div class="col-lg-7" data-aos="fade-up" data-aos-delay="100">
                        <h2 class="inner-title">Nuestra Historia</h2>
                        <div class="our-story">
                            <h4>PrecisoGPS</h4>
                            <p>Somos una empresa con más de 23 años de experiencia en el mercado, especializada en brindar
                                soluciones tecnológicas a medida. Nuestro compromiso es ofrecer productos de calidad,
                                desarrollados internamente, sin intermediarios, para garantizar la satisfacción de nuestros
                                clientes.</p>

                            <ul>
                                <li><i class="bi bi-check-circle"></i> Servicios innovadores y personalizados.</li>
                                <li><i class="bi bi-check-circle"></i> Compromiso con el desarrollo tecnológico nacional.
                                </li>
                                <li><i class="bi bi-check-circle"></i> Calidad y confiabilidad en cada solución.</li>
                            </ul>

                            <p>Desde nuestros inicios, hemos participado en proyectos destacados que han permitido el
                                desarrollo tecnológico en el sector público y privado, consolidando nuestra posición como
                                líderes en la industria.</p>

                        </div>
                    </div>

                </div>

            </div>

        </section><!-- /About Section -->

        <!-- Timeline Section -->
        {{-- <section id="timeline" class="timeline section light-background"> --}}
            <section class="section">
            
            <div class="container">
                <ul class="timeline">
                    <li>
                        <div class="timeline-image"><img class="rounded-circle img-fluid"
                                src="{{ asset('img/about/4.jpg') }}" alt="Misión" /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>Misión</h4>
                                <h4 class="subheading">Compromiso con la tecnología nacional</h4>
                            </div>
                            <div class="timeline-body">
                                <p>Brindar servicios de monitoreo en el transporte público y privado con productos
                                    tecnológicos
                                    desarrollados a la medida, permitiendo a nuestros clientes adquirirlos sin
                                    intermediarios y
                                    con excelente calidad.</p>
                            </div>
                        </div>
                    </li>
                    <li class="timeline-inverted">
                        <div class="timeline-image"><img class="rounded-circle img-fluid"
                                src="{{ asset('img/about/3.jpg') }}" alt="Visión" /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>Visión</h4>
                                <h4 class="subheading">Liderazgo en desarrollo tecnológico</h4>
                            </div>
                            <div class="timeline-body">
                                <p>Ser pioneros en tecnología que permita satisfacer necesidades en hardware y software
                                    nacional,
                                    con miras a lograr certificaciones ISO para 2030, impulsando proyectos en empresas
                                    públicas y
                                    privadas tanto nacionales como internacionales.</p>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="timeline-image"><img class="rounded-circle img-fluid"
                                src="{{ asset('img/about/2.jpg') }}" alt="Proyectos de éxito" /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>Proyectos de Éxito</h4>
                                <h4 class="subheading">Innovación aplicada en grandes proyectos</h4>
                            </div>
                            <div class="timeline-body">
                                <p>Hemos participado en proyectos como la instalación de sistemas de monitoreo en el
                                    Teleférico de
                                    Quito, soluciones de conteo de pasajeros en el transporte público de Quito, y sistemas
                                    de
                                    control para tanqueros en petroleras.</p>
                            </div>
                        </div>
                    </li>
                    <li class="timeline-inverted">
                        <div class="timeline-image">
                            <h4>
                                ¡Sé Parte
                                <br />
                                De Nuestra
                                <br />
                                Historia!
                            </h4>
                        </div>
                    </li>
                </ul>
            </div>
        </section><!-- /Timeline Section -->
        <section id="clients" class="clients section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Nuestros clientes</h2>
            </div><!-- End Section Title -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row g-0 clients-wrap">

                    <div class="col-xl-3 col-md-4 client-logo">
                        <img src="{{ asset('img/clients/quitumbe.png') }}" class="img-fluid" alt="">
                    </div><!-- End Client Item -->

                    <div class="col-xl-3 col-md-4 client-logo">
                        <img src="{{ asset('img/clients/Kinara.jpg') }}" class="img-fluid" alt="">
                    </div><!-- End Client Item -->

                    <div class="col-xl-3 col-md-4 client-logo">
                        <img src="{{ asset('img/clients/Rutvitransa.png') }}" class="img-fluid" alt="">
                    </div><!-- End Client Item -->

                    <div class="col-xl-3 col-md-4 client-logo">
                        <img src="{{ asset('img/clients/Sirena.jpg') }}" class="img-fluid" alt="">
                    </div><!-- End Client Item -->

                    <div class="col-xl-3 col-md-4 client-logo">
                        <img src="{{ asset('img/clients/teleferico.png') }}" class="img-fluid" alt="">
                    </div><!-- End Client Item -->

                    <div class="col-xl-3 col-md-4 client-logo">
                        <img src="{{ asset('img/clients/Transporsel.jpg') }}" class="img-fluid" alt="">
                    </div><!-- End Client Item -->

                    <div class="col-xl-3 col-md-4 client-logo">
                        <img src="{{ asset('img/clients/xtrim.png') }}" class="img-fluid" alt="">
                    </div><!-- End Client Item -->

                    <div class="col-xl-3 col-md-4 client-logo">
                        <img src="{{ asset('img/clients/Transalfa.jpg') }}" class="img-fluid" alt="">
                    </div><!-- End Client Item -->
                    <div class="col-xl-3 col-md-4 client-logo">
                        <img src="{{ asset('img/clients/TransPerifericos.png') }}" class="img-fluid" alt="">
                    </div><!-- End Client Item -->

                </div>

            </div>

        </section><!-- /Clients Section -->

    </main>
@endsection
