@extends('layout') {{-- Extiende el layout principal --}}
@section('Titulo', 'PrecisoGPS - Login')
@section('content')
    <main class="main">
        <!-- Page Title -->
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Login</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Login</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <div class="login-container">
            <div class="login-card">
                <h2 class="login-title">Iniciar Sesión</h2>

                @if (session('error'))
                    <div class="alert alert-danger text-center">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Formulario de Login -->
                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf

                    <!-- Campo Correo Electrónico -->
                    <div class="form-group">
                        <label for="CEDULA" class="form-label">Usuario</label>
                        <input type="text" id="CEDULA" name="CEDULA" class="form-input"
                            placeholder="Ej: 1234567890" required>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="form-group">
                        <label for="CLAVE" class="form-label">Contraseña</label>
                        <div class="password-container">
                            <input type="password" id="CLAVE" name="CLAVE" class="form-input" placeholder="Tu contraseña" required>
                            <span class="toggle-password" onclick="togglePassword()">👁️</span>
                        </div>
                    </div>

                    <!-- Botón de Inicio de Sesión -->
                    <button type="submit" class="login-button">Iniciar Sesión</button>

                    <!-- Enlaces adicionales -->
                    <div class="login-links">
                        <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('CLAVE');
            const passwordToggle = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.textContent = '🙈'; // Cambia el ícono
            } else {
                passwordInput.type = 'password';
                passwordToggle.textContent = '👁️'; // Cambia el ícono
            }
        }
    </script>
@endsection
