@extends('layout')
@section('Titulo', 'PrecisoGPS - Blog')
@section('content')

    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Editar Blog</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current">Blog</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">
                <!-- Botón Nuevo Blog (antes de la lista) -->
                <div class="text-end mb-4">
                    <a href="{{ route('blog.create') }}" class="btn btn-success">+ Nuevo Blog</a>
                </div>

                @foreach ($blogs as $blog)
                    <div class="card mb-4">
                        @if ($blog->URL_IMAGEN)
                            <img src="{{ asset('back/storage/app/public/' . $blog->URL_IMAGEN) }}"
                                class="card-img-top blog-img">
                        @endif
                        <div class="card-body">
                            <h3>{{ $blog->TITULO }}</h3>
                            <p>{{ Str::limit($blog->CONTENIDO, 150) }}</p>

                            <!-- Botones de Acciones -->
                            <div class="d-flex gap-2">
                                <a href="{{ route('blog.edit', $blog->BLO_ID) }}" class="btn btn-warning">Editar</a>
                                <form action="{{ route('blog.destroy', $blog->BLO_ID) }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de eliminar este blog?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Eliminar Blog</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </main>

    <!-- Estilos para mejorar la presentación -->
    <style>
        .blog-img {
            max-height: 250px;
            /* Define la altura máxima */
            object-fit: cover;
            /* Asegura que la imagen se vea bien */
            width: 100%;
            /* Que no se pase del contenedor */
            border-radius: 10px;
            /* Opcional: bordes redondeados */
        }
    </style>

@endsection
