@extends('layout')

@section('Titulo', 'Editar Característica')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Editar Característica</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('plan.index') }}">Planes</a></li>
                        <li class="current">Editar Característica</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="container">
                <form action="{{ route('caracteristica.update', $caracteristica->CAR_ID) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Campo oculto para la URL de referencia -->
                    <input type="hidden" name="previous_url" value="{{ $previousUrl }}">

                    <div class="mb-3">
                        <label for="DESCRIPCION" class="form-label">Descripción de la Característica</label>
                        <textarea class="form-control" id="DESCRIPCION" name="DESCRIPCION" rows="3" required>{{ $caracteristica->DESCRIPCION }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="{{ $previousUrl }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </section>
    </main>
@endsection
