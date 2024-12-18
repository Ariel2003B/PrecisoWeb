<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    {{-- <title>PrecisoGPS - Política de Privacidad</title> --}}
    <title>@yield('Titulo')</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logoPreciso.jpg') }}" />
    <!-- Font Awesome icons (free version)-->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
    <!-- Core theme CSS (includes Bootstrap)-->
    {{-- <link href="css/styles.css" rel="stylesheet" /> --}}
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
</head>

<body id="page-top">
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="" href="{{ route('home.inicio') }}">
                <img src="{{ asset('img/PrecisNavidad.png') }}" alt="Preciso GPS" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                Menu
                <i class="fas fa-bars ms-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                    <li class="nav-item"><a class="nav-link @yield('ActivarAccessC')"
                            href="{{ route('home.accesoCliente') }}">Acceso
                            clientes</a></li>
                    <li class="nav-item"><a class="nav-link @yield('ActivarServicios')"
                            href="{{ route('home.servicios') }}">Servicios</a>
                    </li>
                    <li class="nav-item"><a class="nav-link @yield('ActivarPlanes')"
                            href="{{ route('home.planes') }}">Planes</a>
                    </li>
                    <li class="nav-item"><a class="nav-link @yield('ActivarNosotros')"
                            href="{{ route('home.nosotros') }}">Nosotros</a></li>
                    </li>

                    <li class="nav-item"><a class="nav-link @yield('ActivarPV')"
                            href="{{ route('home.privacidad') }}">Política
                            de privacidad</a></li>
                    @if (Auth::check())
                        <li class="nav-item dropdown-custom">
                            <a href="#" class="nav-link dropdown-toggle-custom">{{ Auth::user()->NOMBRE }}
                                {{ Auth::user()->APELLIDO }}
                            <i class="fas fa-chevron-down"></i>
                            </a>

                            <ul class="dropdown-menu-custom">
                                @if (Auth::user()->p_e_r_f_i_l->p_e_r_m_i_s_o_s->contains('DESCRIPCION', 'USUARIOS'))
                                    <li><a href="">Usuarios</a></li>
                                @endif
                                @if (Auth::user()->p_e_r_f_i_l->p_e_r_m_i_s_o_s->contains('DESCRIPCION', 'SIMCARDS'))
                                    <li><a href="">Simcards</a></li>
                                @endif
                                @if (Auth::user()->p_e_r_f_i_l->p_e_r_m_i_s_o_s->contains('DESCRIPCION', 'VEHICULOS'))
                                    <li><a href="">Vehículos</a></li>
                                @endif
                                @if (Auth::user()->p_e_r_f_i_l->p_e_r_m_i_s_o_s->contains('DESCRIPCION', 'PERFILES'))
                                    <li><a href="">Perfiles</a></li>
                                @endif



                                <li><a class="logout-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar
                                        sesión</a>
                                </li>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </ul>
                        </li>

                        {{-- 
                        <li class="nav-item">
                            <a class="nav-link @yield('SessionEnd')" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Cerrar sesión
                            </a>

                        </li> --}}
                    @else
                        <li class="nav-item"><a class="nav-link @yield('ActivarLogin')"
                                href="{{ route('login.form') }}">Iniciar
                                sesion</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
    @yield ('content')
    <!-- Footer -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 text-lg-start">Copyright &copy; PrecisoGPS 2024</div>
                <div class="col-lg-4 my-3 my-lg-0">
                    <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Twitter"><i
                            class="fab fa-twitter"></i></a>
                    <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Facebook"><i
                            class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="LinkedIn"><i
                            class="fab fa-linkedin-in"></i></a>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a class="link-dark text-decoration-none me-3" href="policyandprivacy.html">Política de
                        privacidad</a>
                </div>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/593990453275?text=Hola,%20me%20gustaría%20saber%20más%20sobre%20sus%20servicios%20de%20rastreo%20y%20monitoreo."
        class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    @if (View::hasSection('jsCode'))
        <script src="{{ asset(View::getSection('jsCode')) }}"></script>
    @endif


    <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>

    <script>
        fetch('/incrementar-visitas')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(() => fetch('/obtener-visitas'))
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('contador-visitas').innerText =
                    `Tú eres el visitante número ${data.contador} de PrecisoGPS`;
            })
            .catch(error => {
                console.error('Error al cargar el contador de visitas:', error);
            });
    </script>
</body>

</html>
