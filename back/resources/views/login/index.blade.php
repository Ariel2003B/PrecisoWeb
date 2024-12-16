@extends('layout') {{-- Extiende el layout principal --}}

@section('Titulo', 'Iniciar Sesión')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; margin-top: 80px;">
        <div class="card shadow-lg rounded w-100" style="max-width: 500px;">
            <!-- Cabecera del formulario -->
            <div class="card-header text-center text-white py-3" style="background-color: #005298;">
                <h2 class="fw-bold mb-0">¡Bienvenido!</h2>
                <p class="mb-0" style="font-size: 0.9rem;">Inicia sesión para continuar</p>
            </div>

            <!-- Cuerpo del formulario -->
            <div class="card-body px-4 py-4">
                @if (session('error'))
                    <div class="alert alert-danger text-center">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Formulario de Login -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Campo Correo Electrónico -->
                    <div class="mb-3">
                        <label for="CORREO" class="form-label fw-semibold">Correo Electrónico</label>
                        <input type="email" id="CORREO" name="CORREO" class="form-control rounded-pill"
                            placeholder="Ej: ejemplo@correo.com" required>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="mb-3">
                        <label for="CLAVE" class="form-label fw-semibold">Contraseña</label>
                        <input type="password" id="CLAVE" name="CLAVE" class="form-control rounded-pill"
                            placeholder="Tu contraseña" required>
                    </div>

                    <!-- Botón de Inicio de Sesión -->
                    <div class="d-grid">
                        <button type="submit" class="btn text-white rounded-pill py-2 fw-bold"
                            style="background-color: #005298;">Iniciar Sesión</button>
                    </div>

                    <!-- Enlaces adicionales -->
                    <div class="text-center mt-3">
                        <a href="#" class="text-decoration-none text-muted small">¿Olvidaste tu contraseña?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Margen superior para evitar que el formulario se solape con el menú */
        body {
            margin-top: 80px;
            /* Ajustar según la altura de tu navbar */
        }

        /* Ajustes responsivos para el formulario */
        @media (max-width: 768px) {
            .card {
                margin: 0 10px;
                /* Espaciado horizontal en pantallas pequeñas */
            }

            .navbar-brand img {
                max-width: 120px;
                /* Reducir el tamaño del logo */
            }
        }
    </style>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
