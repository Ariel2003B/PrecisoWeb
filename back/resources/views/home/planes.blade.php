@extends('layout')
@section('Titulo', 'PrecisoGPS - Planes')
@section('ActivarPlanes', 'active')
@section('content')
    <main class="main">

        <!-- Page Title -->
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Planes</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Planes</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <!-- Pricing Section -->
        <section id="pricing" class="pricing section">

            <div class="container">

                <div class="row gy-4">
                    @foreach ($planes as $plan)
                        <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="100">
                            <div class="pricing-item">
                                <h3>{{ $plan->NOMBRE }}</h3>
                                <p class="description">{{ $plan->DESCRIPCION }}</p>
                                <h4><sup>$</sup>{{ $plan->PRECIO }}</h4>
                                <a href="#" class="cta-btn add-to-cart" data-id="{{ $plan->PLA_ID }}"
                                    data-name="{{ $plan->NOMBRE }}" data-price="{{ $plan->PRECIO }}"
                                    data-time="{{ $plan->TIEMPO }}">Añadir al carrito</a>
                                <ul>
                                    @foreach ($plan->c_a_r_a_c_t_e_r_i_s_t_i_c_a_s as $caracteristica)
                                        @if ($caracteristica->pivot->POSEE)
                                            <li><i class="bi bi-check"></i> <span>{{ $caracteristica->DESCRIPCION }}</span>
                                            </li>
                                        @else
                                            <li class="na"><i class="bi bi-x"></i>
                                                <span>{{ $caracteristica->DESCRIPCION }}</span></li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div><!-- End Pricing Item -->
                    @endforeach
                </div>
            </div>
        </section><!-- /Pricing Section -->
    </main>
    @php
        $numeroWhatsApp = '+593990453275'; // Número de WhatsApp
        $nombreUsuario = 'Santiago'; // Nombre del usuario
        $servicios = [
            [
                'nombre' => 'Rastreo Satelital',
                'descripcion' => 'Monitoreo en tiempo real de vehículos a través de GPS satelital.',
                'icono' => 'bi-geo-alt',
                'mensaje' => "Hola $nombreUsuario, espero que estés teniendo un buen día. Me gustaría obtener más información sobre el servicio de Rastreo Satelital. ¿Podrías darme más detalles?",
                'color' => 'item-cyan',
            ],
            [
                'nombre' => 'Cámaras de Seguridad',
                'descripcion' =>
                    'Instalación de cámaras de seguridad en vehículos para mayor control y vigilancia en tiempo real.',
                'icono' => 'bi-camera-video',
                'mensaje' => "Hola $nombreUsuario, me interesa el servicio de Cámaras de Seguridad para mi vehículo. ¿Podrías enviarme más información sobre precios y características?",
                'color' => 'item-orange',
            ],
            [
                'nombre' => 'Soluciones Tecnológicas',
                'descripcion' =>
                    'Desarrollo de soluciones IoT personalizadas, incluyendo sistemas de seguridad y monitoreo.',
                'icono' => 'bi-gear',
                'mensaje' => "Hola $nombreUsuario, quiero conocer más sobre las Soluciones Tecnológicas que ofrecen. ¿Pueden ayudarme a desarrollar una solución a medida?",
                'color' => 'item-teal',
            ],
            [
                'nombre' => 'Contadores de Pasajeros',
                'descripcion' =>
                    'Sistema de conteo de pasajeros para transporte público, permitiendo un control preciso y seguro.',
                'icono' => 'bi-person-check',
                'mensaje' => "Hola $nombreUsuario, estoy interesado en el sistema de Contadores de Pasajeros. ¿Podrías explicarme cómo funciona y qué beneficios ofrece?",
                'color' => 'item-red',
            ],
            [
                'nombre' => 'Sistemas Solares',
                'descripcion' =>
                    'Implementación de sistemas solares personalizados para diferentes necesidades energéticas.',
                'icono' => 'bi-sun',
                'mensaje' => "Hola $nombreUsuario, quiero saber más sobre los Sistemas Solares que ofrecen. ¿Me podrías enviar detalles sobre costos y beneficios?",
                'color' => 'item-indigo',
            ],
            [
                'nombre' => 'Centros de Monitoreo',
                'descripcion' =>
                    'Centros de monitoreo 24/7 para supervisión de seguridad en tiempo real con tecnología avanzada.',
                'icono' => 'bi-building-lock', // Icono actualizado
                'mensaje' => "Hola $nombreUsuario, quiero información sobre los Centros de Monitoreo que ofrecen. ¿Podrían explicarme cómo funciona el servicio y qué beneficios proporciona?",
                'color' => 'item-pink',
            ],
        ];
    @endphp

        <!-- Services Section -->
        <section id="services" class="services section light-background">
            <div class="container">
                <div class="row gy-4">
                    @foreach ($servicios as $servicio)
                        @php
                            $mensajeWhatsApp = urlencode($servicio['mensaje']);
                            $urlWhatsApp = "https://wa.me/$numeroWhatsApp?text=$mensajeWhatsApp";
                        @endphp

                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                            <div class="service-item {{ $servicio['color'] }} position-relative">
                                <div class="icon">
                                    <svg width="100" height="100" viewBox="0 0 600 600"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke="none" stroke-width="0" fill="#f5f5f5"
                                            d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174">
                                        </path>
                                    </svg>
                                    <i class="bi {{ $servicio['icono'] }}"></i>
                                </div>
                                <a href="{{ $urlWhatsApp }}" class="stretched-link" target="_blank">
                                    <h3>{{ $servicio['nombre'] }}</h3>
                                </a>
                                <p>{{ $servicio['descripcion'] }}</p>
                            </div>
                        </div><!-- End Service Item -->
                    @endforeach
                </div>
            </div>
        </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    debugger;
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const price = this.dataset.price;
                    const time = this.dataset.time;

                    fetch('carrito/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                id,
                                name,
                                price,
                                time
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('cart-count').textContent = data.cartCount;
                            alert(data.message);
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
@endsection
