@extends('layout')
@section('Titulo', 'Eliminar cuenta - iGO')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Solicitar eliminación de cuenta</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Eliminar cuenta</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Formulario</h5>
                                <p class="text-muted">
                                    Completa el formulario y enviaremos tu solicitud a nuestro sistema.
                                </p>

                                <div id="alert-ok" class="alert alert-success d-none"></div>
                                <div id="alert-err" class="alert alert-danger d-none"></div>

                                <form id="deletion-form">
                                    <div class="mb-3">
                                        <label class="form-label">Correo asociado a tu cuenta</label>
                                        <input type="email" id="email" class="form-control" required
                                            placeholder="tucorreo@dominio.com">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Motivo (opcional)</label>
                                        <textarea id="reason" rows="3" class="form-control" placeholder="Cuéntanos si deseas..."></textarea>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="confirm" required>
                                        <label class="form-check-label" for="confirm">
                                            Confirmo que deseo eliminar mi cuenta y entiendo que es irreversible.
                                        </label>
                                    </div>

                                    <button id="sendBtn" type="submit" class="btn btn-danger">
                                        Enviar solicitud
                                    </button>
                                </form>

                                <small class="text-muted d-block mt-3">
                                    Si tienes problemas con el formulario, escríbenos a
                                    <a href="mailto:soporte@igoservice.ec">soporte@igoservice.ec</a>.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">¿Qué verás en este proceso?</h5>
                                <ul>
                                    <li>Verificaremos tu identidad por correo.</li>
                                    <li>Tu cuenta se desactivará y programaremos la eliminación.</li>
                                    <li>Plazo estimado de procesamiento: <strong>hasta 30 días</strong>.</li>
                                </ul>

                                <h6 class="mt-4">¿Qué datos se eliminan?</h6>
                                <ul>
                                    <li>Perfil (nombre, apellidos, correo, teléfono, cédula y direcciones).</li>
                                    <li>Tokens/sesiones y adjuntos asociados a tu cuenta.</li>
                                    <li>Historial operativo: se <em>anonimiza o desvincula</em> de tu identidad.</li>
                                </ul>

                                <h6 class="mt-4">¿Qué se conserva y por cuánto?</h6>
                                <ul>
                                    <li>Información fiscal/contable: hasta <strong>5 años</strong> (por ley).</li>
                                    <li>Registros técnicos (logs): hasta <strong>90 días</strong>.</li>
                                    <li>Estadísticas agregadas no identificables: indefinido.</li>
                                </ul>

                                <p class="mt-3">
                                    Consulta nuestra <a href="{{ route('home.privacidad') }}" target="_blank">Política de
                                        privacidad</a>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    {{-- Configura tu API base en .env y pásala aquí si quieres automatizar --}}
    <script>
        // Cambia esta constante a tu dominio de API backend (el que usa la app móvil).
        const API_BASE = '{{ config('app.env') === 'local' ? 'http://localhost:8000' : 'https://api.tu-dominio.com' }}';
        const ENDPOINT = API_BASE + '/api/account/deletion-request';

        const form = document.getElementById('deletion-form');
        const email = document.getElementById('email');
        const reason = document.getElementById('reason');
        const confirmCk = document.getElementById('confirm');
        const btn = document.getElementById('sendBtn');
        const okBox = document.getElementById('alert-ok');
        const errBox = document.getElementById('alert-err');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            okBox.classList.add('d-none');
            errBox.classList.add('d-none');

            if (!confirmCk.checked) {
                errBox.textContent = 'Debes confirmar que deseas eliminar tu cuenta.';
                errBox.classList.remove('d-none');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Enviando...';

            try {
                const res = await fetch(ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    // Si tu API requiere token CSRF u otro header, agrégalo aquí
                    body: JSON.stringify({
                        email: email.value.trim(),
                        reason: reason.value.trim(),
                        source: 'web',
                        platform: 'android' // sugerido para Play Console
                    })
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok && (data.ok === true || data.status === 'ok')) {
                    okBox.textContent = data.message ||
                        'Solicitud recibida. Te contactaremos por correo para confirmar.';
                    okBox.classList.remove('d-none');
                    form.reset();
                } else {
                    errBox.textContent = (data.message ||
                        'No pudimos procesar la solicitud. Intenta más tarde.');
                    errBox.classList.remove('d-none');
                }
            } catch (err) {
                errBox.textContent = 'Error de conexión. Intenta nuevamente.';
                errBox.classList.remove('d-none');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Enviar solicitud';
            }
        });
    </script>
@endsection
