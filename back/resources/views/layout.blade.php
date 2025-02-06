<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>@yield('Titulo')</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- Favicons -->
    <link href="{{ asset('img/precisoimg/logoPreciso.jpg') }}" rel="icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/aos/aos.css" rel="stylesheet') }}">
    <link href="{{ asset('vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- =======================================================
  * Template Name: Company
  * Template URL: https://bootstrapmade.com/company-free-html-bootstrap-template/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container position-relative d-flex align-items-center">

            <a href="{{ route('home.inicio') }}" class="logo d-flex align-items-center me-auto">
                <img src="{{ asset('img/Precisogps.png') }}" alt="">
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="{{ route('home.inicio') }}" class="@yield('ActivarInicio')">Inicio</a></li>
                    <li><a href="{{ route('home.servicios') }}" class="@yield('ActivarServicios')">Servicios</a></li>
                    <li><a href="{{ route('home.equipos') }}" class="@yield('ActivarEquipos')">Equipos y accesorios</a></li>
                    <li><a href="{{ route('home.planes') }}" class="@yield('ActivarPlanes')">Planes</a></li>
                    <li><a href="{{ route('home.blogs') }}" class="@yield('ActivarBlog')">Blog</a></li>
                    <li><a href="{{ route('home.nosotros') }}" class="@yield('ActivarNosotros')">Nosotros</a></li>
                    <!-- Dropdown del usuario -->
                    <li><a href="{{ route('home.plataformas') }}" class="@yield('ActivarPlataformas')">Plataformas</a></li>
                    @if (Auth::check())
                        <li class="dropdown"><a><span>{{ Auth::user()->NOMBRE }}
                                    {{ Auth::user()->APELLIDO }}</span> <i
                                    class="bi bi-chevron-down toggle-dropdown"></i></a>
                            <ul>
                                <li><a onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        href="{{ route('logout') }}">Cerrar sesión</a></li>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </ul>
                        </li>
                    @endif
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            <a href="{{ route('carrito.index') }}" class="cart-icon">
                <i class="bi bi-cart4"></i>
                <span id="cart-count"
                    class="cart-count">{{ session('carrito') ? count(session('carrito')) : 0 }}</span>
            </a>
            {{-- <div class="header-social-links">
                <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
                <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
            </div> --}}
        </div>
    </header>

    @yield('content')

    <footer id="footer" class="footer dark-background">
        <div class="container footer-top">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6 footer-about">
                    <a href="index.html" class="logo d-flex align-items-center">
                        <span class="sitename">PrecisoGPS</span>
                    </a>
                    <div class="footer-contact pt-3">
                        <p>E16 N53-209 y de los Cholanes</p>
                        <p>Quito, 170514</p>
                        <p class="mt-3"><strong>Celular:</strong> <span>+593 99 045 3275</span></p>
                        <p><strong>Correo:</strong> <span>ventas@precisogps.com</span></p>
                    </div>
                    <div class="social-links d-flex mt-4">

                        <a href="https://www.facebook.com/PrecisoGPS?locale=es_LA" target="_blank"><i
                                class="bi bi-facebook"></i></a>
                        <a href="https://www.instagram.com/precisogps/" target="_blank"><i
                                class="bi bi-instagram"></i></a>
                        <a href="https://www.youtube.com/@PrecisoGPS" target="_blank"><i class="bi bi-youtube"></i></a>
                        <a href="https://www.tiktok.com/@precisogps" target="_blank"><i class="bi bi-tiktok"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Enlaces utiles</h4>
                    <ul>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('home.planes') }}">Planes</a></li>
                        <li><a href="{{ route('home.privacidad') }}">Politica de privacidad</a></li>
                        <li><a href="#">FAQ y manual de usuario y videos de capacitacion</a></li>
                    </ul>
                </div>

                {{-- <div class="col-lg-3 col-md-12 footer-newsletter">
                    <h4>¿Quieres recibir nuestras actualizaciones?</h4>
                    <p>Suscribete y recibe notificaciones de nuestras ultimas actualizaciones, planes y productos.</p>
                    <form action="forms/newsletter.php" method="post" class="php-email-form">
                        <div class="newsletter-form"><input type="email" name="email"><input type="submit"
                                value="Registrar"></div>
                        <div class="loading">Cargando</div>
                        <div class="error-message"></div>
                        <div class="sent-message">Tu suscripción ha sido registrada, Gracias!</div>
                    </form>
                </div> --}}
                <div class="col-lg-3 col-md-12 footer-newsletter">
                    <h4>¿Quieres recibir nuestras actualizaciones?</h4>
                    <p>Suscríbete y recibe notificaciones de nuestras últimas actualizaciones, planes y productos.</p>

                    <form id="newsletter-form">


                        <div class="newsletter-form">
                            <input type="email" id="email" name="email" placeholder="Tu correo electrónico"
                                required>
                            <input type="submit" id="subscribe-button" value="Registrar" disabled>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="accept-privacy">
                            <label class="form-check-label" for="accept-privacy">
                                Acepto las <a href="{{ route('home.privacidad') }}" target="_blank">políticas de
                                    privacidad</a>.
                            </label>
                        </div>
                        <div class="loading" style="display: none;">Cargando...</div>
                        <div class="error-message" style="color: red;"></div>
                        <div class="sent-message" style="color: green;"></div>
                    </form>
                </div>

                <div class="col-lg-3 col-md-4 footer-newsletter">

                </div>
            </div>
        </div>

        <div class="container copyright text-center mt-4">
            <p>© <span>Copyright</span> <strong class="px-1 sitename">PrecisoGPS</strong> <span>Todos los derechos
                    reservados.</span>
            </p>
        </div>

    </footer>
    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>
    <!-- Botón flotante de WhatsApp -->
    <!-- Botón flotante de WhatsApp con mensaje predefinido -->
    <a href="https://wa.me/593990453275?text=Hola%2C%20me%20interesa%20asesoramiento%20sobre%20el%20rastreo%20vehicular.%20¿Podrías%20darme%20más%20información%3F"
        target="_blank" class="whatsapp-float">
        <i class="bi bi-whatsapp"></i>
    </a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('vendor/waypoints/noframework.waypoints.js') }}"></script>
    <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
    <!-- Main JS File -->
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("newsletter-form");
            const emailInput = document.getElementById("email");
            const privacyCheckbox = document.getElementById("accept-privacy");
            const submitButton = document.getElementById("subscribe-button");
            const loading = document.querySelector(".loading");
            const errorMessage = document.querySelector(".error-message");
            const sentMessage = document.querySelector(".sent-message");

            // Habilitar el botón solo si se acepta la política
            privacyCheckbox.addEventListener("change", function() {
                submitButton.disabled = !this.checked;
            });

            // Manejo del formulario con AJAX
            form.addEventListener("submit", function(event) {
                event.preventDefault();

                loading.style.display = "block";
                errorMessage.style.display = "none";
                sentMessage.style.display = "none";

                fetch("{{ route('newsletter.subscribe') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            email: emailInput.value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        loading.style.display = "none";
                        if (data.message) {
                            sentMessage.textContent = data.message;
                            sentMessage.style.display = "block";
                            emailInput.value = "";
                            privacyCheckbox.checked = false;
                            submitButton.disabled = true;
                        }
                    })
                    .catch(error => {
                        loading.style.display = "none";
                        errorMessage.textContent = "Error al suscribirse. Inténtalo de nuevo.";
                        errorMessage.style.display = "block";
                    });
            });
        });
    </script>




</body>

</html>
