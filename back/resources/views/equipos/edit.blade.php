@extends('layout')

@section('Titulo', 'Editar Equipo o Accesorio')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Editar Equipo o Accesorio</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li><a href="{{ route('equipos.index') }}">Equipos y Accesorios</a></li>
                        <li class="current">Editar</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card shadow-sm p-4">
                            <h2 class="fw-bold text-center mb-4">Editar Información</h2>
                            <form action="{{ route('equipos.update', $equipo->EQU_ID) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Nombre -->
                                <div class="mb-3">
                                    <label for="EQU_NOMBRE" class="form-label fw-bold">Nombre del Equipo</label>
                                    <input type="text" name="EQU_NOMBRE" id="EQU_NOMBRE" class="form-control"
                                        value="{{ $equipo->EQU_NOMBRE }}" required>
                                </div>

                                <!-- Precio -->
                                <div class="mb-3">
                                    <label for="EQU_PRECIO" class="form-label fw-bold">Precio</label>
                                    <input type="number" name="EQU_PRECIO" id="EQU_PRECIO" class="form-control"
                                        value="{{ $equipo->EQU_PRECIO }}" step="0.01" required>
                                </div>

                                <!-- Icono -->
                                <div class="mb-3">
                                    <label for="EQU_ICONO" class="form-label fw-bold">Ícono (Bootstrap Icons o Font
                                        Awesome)</label>
                                    <div class="input-group">
                                        <input type="text" name="EQU_ICONO" id="EQU_ICONO" class="form-control"
                                            value="{{ $equipo->EQU_ICONO }}" oninput="updateIconPreview()">
                                        <span class="input-group-text">
                                            <i id="icon-preview" class="{{ $equipo->EQU_ICONO }} fs-3"></i>
                                        </span>
                                    </div>
                                    <small class="form-text text-muted">
                                        Puedes encontrar íconos en:
                                    </small>
                                    <div class="d-flex gap-2 mt-2">
                                        <a href="https://icons.getbootstrap.com/" target="_blank"
                                            class="btn btn-outline-primary w-50">
                                            <i class="bi bi-bootstrap"></i> Bootstrap Icons
                                        </a>
                                        <a href="https://fontawesome.com/icons" target="_blank"
                                            class="btn btn-outline-dark w-50">
                                            <i class="fa-solid fa-font-awesome"></i> Font Awesome
                                        </a>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('equipos.index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Actualizar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function updateIconPreview() {
            let iconInput = document.getElementById('EQU_ICONO').value;
            let iconPreview = document.getElementById('icon-preview');

            if (iconInput.includes('bi-')) {
                iconPreview.className = iconInput + " fs-3";
            } else if (iconInput.includes('fa-')) {
                iconPreview.className = iconInput + " fs-3";
            } else {
                iconPreview.className = "bi bi-question-circle fs-3";
            }
        }
    </script>

@endsection
