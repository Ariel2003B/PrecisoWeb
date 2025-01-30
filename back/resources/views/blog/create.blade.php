@extends('layout')
@section('Titulo', 'PrecisoGPS - Crear Blog')
@section('content')

    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Crear Blog</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('blog.index') }}">Blog</a></li>
                        <li class="current">Crear Blog</li>
                    </ol>
                </nav>
            </div>
        </div>
        <section class="section">
            <div class="container">
                @if (session()->has('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>¡Error!</strong> Hay problemas con los datos ingresados:
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('blog.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Título -->
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="TITULO" class="form-control" required>
                    </div>

                    <!-- Autor -->
                    <div class="mb-3">
                        <label class="form-label">Autor</label>
                        <input type="text" name="AUTOR" class="form-control" required>
                    </div>

                    <!-- Categoría -->
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="CATEGORIA" class="form-control" required>
                            <option value="">Seleccione una categoría</option>
                            <option value="Tecnología">Tecnología</option>
                            <option value="Transporte público">Transporte público</option>
                            {{-- <option value="Ciencia">Ciencia</option>
                            <option value="Educación">Educación</option>
                            <option value="Salud">Salud</option> --}}
                        </select>
                    </div>

                    <!-- Contenido -->
                    <div class="mb-3">
                        <label class="form-label">Contenido</label>
                        <textarea name="CONTENIDO" class="form-control" rows="10" required></textarea>
                    </div>

                    <!-- Imagen -->
                    <div class="mb-3">
                        <label class="form-label">Imagen</label>
                        <input type="file" name="URL_IMAGEN" class="form-control">
                    </div>

                    <!-- Subtítulos Dinámicos -->
                    <div class="mb-3">
                        <label class="form-label">Subtítulos</label>
                        <div id="subtitulos-container">
                            <div class="subtitulo-group">
                                <label class="form-label">Subtítulo 1</label>
                                <input type="text" name="subtitulos[]" class="form-control mb-2"
                                    placeholder="Título del subtítulo">
                                <textarea name="textosubtitulos[]" class="form-control" placeholder="Contenido del subtítulo" rows="4"></textarea>
                                <button type="button" class="btn btn-danger remove-subtitulo mt-2">Eliminar</button>
                            </div>
                        </div>
                        <button type="button" id="add-subtitulo" class="btn btn-secondary mt-2">Añadir Subtítulo</button>
                    </div>

                    <!-- Botón de Guardar -->
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </section>
    </main>

    <!-- Script para añadir y eliminar subtítulos dinámicamente -->
    <script>
        document.getElementById("add-subtitulo").addEventListener("click", function() {
            let container = document.getElementById("subtitulos-container");
            let index = container.getElementsByClassName("subtitulo-group").length + 1;
            let div = document.createElement("div");
            div.classList.add("subtitulo-group", "mt-3");
            div.innerHTML = `
            <label class="form-label">Subtítulo ${index}</label>
            <input type="text" name="subtitulos[]" class="form-control mb-2" placeholder="Título del subtítulo">
            <textarea name="textosubtitulos[]" class="form-control" placeholder="Contenido del subtítulo" rows="4"></textarea>
            <button type="button" class="btn btn-danger remove-subtitulo mt-2">Eliminar</button>
        `;
            container.appendChild(div);
        });

        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("remove-subtitulo")) {
                e.target.parentElement.remove();
            }
        });
    </script>

    <!-- Estilos para mejorar la presentación -->
    <style>
        .subtitulo-group {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
    </style>

@endsection
