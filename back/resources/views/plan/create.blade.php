@extends('layout')

@section('Titulo', 'Crear Plan')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Crear Plan</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('plan.index') }}">Planes</a></li>
                        <li class="current">Crear Plan</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="container">
                <form action="{{ route('plan.store') }}" method="POST">
                    @csrf
                    <!-- Campos del plan -->
                    <div class="mb-3">
                        <label for="NOMBRE" class="form-label">Nombre del Plan</label>
                        <input type="text" class="form-control" id="NOMBRE" name="NOMBRE" required>
                    </div>
                    <div class="mb-3">
                        <label for="DESCRIPCION" class="form-label">Descripción</label>
                        <textarea class="form-control" id="DESCRIPCION" name="DESCRIPCION" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="PRECIO" class="form-label">Precio</label>
                        <input type="number" class="form-control" id="PRECIO" name="PRECIO" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="TIEMPO" class="form-label">Duración (en meses)</label>
                        <input type="number" class="form-control" id="TIEMPO" name="TIEMPO" required>
                    </div>

                    <!-- Características -->
                    <div class="mb-4">
                        <h5>Características</h5>
                        <div class="form-group">
                            @foreach ($caracteristicas as $caracteristica)
                                <div class="form-check d-flex align-items-center mb-2">
                                    <input class="form-check-input me-2" type="checkbox"
                                        id="caracteristica_{{ $caracteristica->CAR_ID }}"
                                        name="caracteristicas[{{ $caracteristica->CAR_ID }}]" value="1">
                                    <label class="form-check-label me-3" for="caracteristica_{{ $caracteristica->CAR_ID }}">
                                        {{ $caracteristica->DESCRIPCION }}
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            id="posee_{{ $caracteristica->CAR_ID }}"
                                            name="posee[{{ $caracteristica->CAR_ID }}]" value="1">
                                        <label class="form-check-label"
                                            for="posee_{{ $caracteristica->CAR_ID }}">Posee</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#addCaracteristicaModal">
                            Registrar Características
                        </button>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Plan</button>
                </form>
            </div>
        </section>
    </main>

    <!-- Modal para Registrar Características -->
    <div class="modal fade" id="addCaracteristicaModal" tabindex="-1" aria-labelledby="addCaracteristicaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCaracteristicaModalLabel">Registrar Característica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('caracteristica.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="DESCRIPCION" class="form-label">Descripción de la Característica</label>
                            <textarea type="text" class="form-control" id="DESCRIPCION" name="DESCRIPCION" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
