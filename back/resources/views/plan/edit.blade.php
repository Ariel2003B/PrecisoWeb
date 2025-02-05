@extends('layout')

@section('Titulo', 'Editar Plan')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Editar Plan</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('plan.index') }}">Planes</a></li>
                        <li class="current">Editar Plan</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="container">
                <form action="{{ route('plan.update', $plan->PLA_ID) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="NOMBRE" class="form-label">Nombre del Plan</label>
                        <input type="text" class="form-control" id="NOMBRE" name="NOMBRE" value="{{ $plan->NOMBRE }}"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="DESCRIPCION" class="form-label">Descripción</label>
                        <textarea class="form-control" id="DESCRIPCION" name="DESCRIPCION" rows="3" required>{{ $plan->DESCRIPCION }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="PRECIO" class="form-label">Precio</label>
                        <input type="number" class="form-control" id="PRECIO" name="PRECIO" step="0.01"
                            value="{{ $plan->PRECIO }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="TIEMPO" class="form-label">Duración (en meses)</label>
                        <input type="number" class="form-control" id="TIEMPO" name="TIEMPO" value="{{ $plan->TIEMPO }}"
                            required>
                    </div>

                    <div class="mb-4">
                        <h5>Características</h5>
                        <div class="form-group">
                            @foreach ($caracteristicas as $caracteristica)
                                <div class="row align-items-center mb-2">
                                    <!-- Checkbox y Descripción -->
                                    <div class="col-md-4 d-flex align-items-center">
                                        <input class="form-check-input me-2" type="checkbox"
                                            id="caracteristica_{{ $caracteristica->CAR_ID }}"
                                            name="caracteristicas[{{ $caracteristica->CAR_ID }}]" value="1"
                                            {{ $plan->c_a_r_a_c_t_e_r_i_s_t_i_c_a_s->contains($caracteristica->CAR_ID) ? 'checked' : '' }}>
                                        <label for="caracteristica_{{ $caracteristica->CAR_ID }}">
                                            {{ $caracteristica->DESCRIPCION }}
                                        </label>
                                    </div>

                                    <!-- Campo de orden -->
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" id="orden_{{ $caracteristica->CAR_ID }}"
                                            name="orden[{{ $caracteristica->CAR_ID }}]" min="1"
                                            value="{{ $plan->c_a_r_a_c_t_e_r_i_s_t_i_c_a_s->where('CAR_ID', $caracteristica->CAR_ID)->first()?->pivot->ORDEN ?? '' }}"
                                            placeholder="Orden">
                                    </div>

                                    <!-- Botón Editar -->
                                    <div class="col-md-2 text-center">
                                        <a href="{{ route('caracteristica.edit', $caracteristica->CAR_ID) }}"
                                            class="btn btn-warning btn-sm">Editar</a>
                                    </div>

                                    <!-- Switch Posee -->
                                    <div class="col-md-2 text-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="posee_{{ $caracteristica->CAR_ID }}"
                                                name="posee[{{ $caracteristica->CAR_ID }}]" value="1"
                                                {{ $plan->c_a_r_a_c_t_e_r_i_s_t_i_c_a_s->where('CAR_ID', $caracteristica->CAR_ID)->first()?->pivot->POSEE ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="posee_{{ $caracteristica->CAR_ID }}">Posee</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#addCaracteristicaModal">
                            Registrar Características
                        </button>
                    </div>

                    <button type="submit" class="btn btn-primary">Actualizar Plan</button>
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
                            <input type="text" class="form-control" id="DESCRIPCION" name="DESCRIPCION" required>
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
