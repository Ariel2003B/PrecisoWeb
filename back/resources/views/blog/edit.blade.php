@extends('layout')
@section('Titulo', 'PrecisoGPS - Editar Blog')
@section('content')

    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Editar Blog</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('blog.index') }}">Blog</a></li>
                        <li class="current">Editar Blog</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">
                <form action="{{ route('blog.update', $blog->BLO_ID) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Título -->
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="TITULO" class="form-control" value="{{ $blog->TITULO }}" required>
                    </div>

                    <!-- Autor -->
                    <div class="mb-3">
                        <label class="form-label">Autor</label>
                        <input type="text" name="AUTOR" class="form-control" value="{{ $blog->AUTOR }}" required>
                    </div>

                    <!-- Categoría -->
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="CATEGORIA" class="form-control" required>
                            <option value="">Seleccione una categoría</option>
                            <option value="Tecnología" {{ $blog->CATEGORIA == 'Tecnología' ? 'selected' : '' }}>Tecnología
                            </option>
                            <option value="Ciencia" {{ $blog->CATEGORIA == 'Ciencia' ? 'selected' : '' }}>Ciencia</option>
                            <option value="Educación" {{ $blog->CATEGORIA == 'Educación' ? 'selected' : '' }}>Educación
                            </option>
                            <option value="Salud" {{ $blog->CATEGORIA == 'Salud' ? 'selected' : '' }}>Salud</option>
                        </select>
                    </div>

                    <!-- Contenido -->
                    <div class="mb-3">
                        <label class="form-label">Contenido</label>
                        <textarea name="CONTENIDO" class="form-control" rows="10" required>{{ $blog->CONTENIDO }}</textarea>
                    </div>

                    <!-- Imagen Actual -->
                    @if ($blog->URL_IMAGEN)
                        <div class="mb-3">
                            <label class="form-label">Imagen Actual</label><br>
                            <img src="{{ asset('back/storage/app/public/' . $blog->URL_IMAGEN) }}" alt="Imagen del Blog"
                                class="img-thumbnail" width="200">
                        </div>
                    @endif

                    <!-- Nueva Imagen -->
                    <div class="mb-3">
                        <label class="form-label">Subir Nueva Imagen (Opcional)</label>
                        <input type="file" name="URL_IMAGEN" class="form-control">
                    </div>

                    <!-- Subtítulos Dinámicos -->
                    <div class="mb-3">
                        <label class="form-label">Subtítulos</label>
                        <div id="subtitulos-container">
                            @foreach ($blog->s_u_b_t_i_t_u_l_o_s as $index => $subtitulo)
                                <div class="subtitulo-group" data-sub-id="{{ $subtitulo->SUB_ID }}">
                                    <input type="hidden" name="subtitulos_ids[]" value="{{ $subtitulo->SUB_ID }}">
                                    <label class="form-label">Subtítulo {{ $index + 1 }}</label>
                                    <input type="text" name="subtitulos[{{ $subtitulo->SUB_ID }}]"
                                        class="form-control mb-2" value="{{ $subtitulo->TEXTO }}"
                                        placeholder="Título del subtítulo">
                                    <textarea name="textosubtitulos[{{ $subtitulo->SUB_ID }}]" class="form-control" rows="4"
                                        placeholder="Contenido del subtítulo">{{ $subtitulo->CONTENIDO }}</textarea>
                                    <button type="button" class="btn btn-danger remove-subtitulo mt-2">Eliminar</button>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="deleted_subtitulos" id="deleted_subtitulos">

                        <button type="button" id="add-subtitulo" class="btn btn-secondary mt-2">Añadir Subtítulo</button>
                    </div>

                    <!-- Botón de Guardar -->
                    <button type="submit" class="btn btn-primary">Actualizar Blog</button>
                </form>
            </div>
        </section>
    </main>

    <!-- Script para añadir y eliminar subtítulos dinámicamente -->
    <script>
        let deletedSubtitulos = [];

        document.getElementById("add-subtitulo").addEventListener("click", function() {
            let container = document.getElementById("subtitulos-container");
            let index = container.getElementsByClassName("subtitulo-group").length + 1;
            let div = document.createElement("div");
            div.classList.add("subtitulo-group", "mt-3");
            div.innerHTML = `
                <label class="form-label">Subtítulo ${index}</label>
                <input type="text" name="subtitulos[new_${index}]" class="form-control mb-2" placeholder="Título del subtítulo">
                <textarea name="textosubtitulos[new_${index}]" class="form-control" placeholder="Contenido del subtítulo" rows="4"></textarea>
                <button type="button" class="btn btn-danger remove-subtitulo mt-2">Eliminar</button>
            `;
            container.appendChild(div);
        });

        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("remove-subtitulo")) {
                let subtituloGroup = e.target.parentElement;
                let subId = subtituloGroup.getAttribute("data-sub-id");

                if (subId) {
                    deletedSubtitulos.push(subId);
                    document.getElementById("deleted_subtitulos").value = deletedSubtitulos.join(",");
                }

                subtituloGroup.remove();
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
