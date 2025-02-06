@extends('layout')

@section('Titulo', 'Añadir Nuevo Equipo o Accesorio')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Añadir Nuevo Equipo o Accesorio</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('equipos.index') }}">Equipos y Accesorios</a></li>
                        <li class="current">Añadir</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card shadow-sm p-4">
                            <h2 class="fw-bold text-center mb-4">Nuevo Equipo o Accesorio</h2>
                            <form action="{{ route('equipos.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <!-- Nombre -->
                                <div class="mb-3">
                                    <label for="EQU_NOMBRE" class="form-label fw-bold">Nombre del Equipo</label>
                                    <input type="text" name="EQU_NOMBRE" id="EQU_NOMBRE" class="form-control"
                                        placeholder="Ejemplo: GPS Tracker XT-100" required>
                                </div>

                                <!-- Precio -->
                                <div class="mb-3">
                                    <label for="EQU_PRECIO" class="form-label fw-bold">Precio</label>
                                    <input type="number" name="EQU_PRECIO" id="EQU_PRECIO" class="form-control"
                                        placeholder="Ejemplo: 120.99" step="0.01" required>
                                </div>

                                <!-- Imagen -->
                                <div class="mb-3">
                                    <label for="EQU_ICONO" class="form-label fw-bold">Imagen del Equipo</label>
                                    <input type="file" name="EQU_ICONO" id="EQU_ICONO" class="form-control" accept="image/*" onchange="previewImage()">
                                    <small class="form-text text-muted">Sube una imagen en formato JPG, PNG o JPEG.</small>
                                    <div class="mt-3 text-center">
                                        <img id="image-preview" src="{{ asset('images/no-image.png') }}" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('equipos.index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function previewImage() {
            let file = document.getElementById('EQU_IMAGEN').files[0];
            let preview = document.getElementById('image-preview');

            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    preview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = "{{ asset('images/no-image.png') }}";
            }
        }
    </script>
@endsection
