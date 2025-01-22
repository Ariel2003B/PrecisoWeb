@extends('layout')

@section('Titulo', 'Listado de Planes')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Planes</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current">Planes</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="container">
                <div class="mb-4">
                    <a href="{{ route('plan.create') }}" class="btn btn-primary">Crear Nuevo Plan</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Precio</th>
                                <th>Duración</th>
                                <th>Características</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($planes as $plan)
                                <tr>
                                    <td>{{ $plan->PLA_ID }}</td>
                                    <td>{{ $plan->NOMBRE }}</td>
                                    <td>{{ $plan->DESCRIPCION }}</td>
                                    <td>${{ number_format($plan->PRECIO, 2) }}</td>
                                    <td>{{ $plan->TIEMPO }}</td>
                                    <td>
                                        <ul>
                                            @foreach ($plan->c_a_r_a_c_t_e_r_i_s_t_i_c_a_s as $caracteristica)
                                                <li>
                                                    {{ $caracteristica->DESCRIPCION }} - 
                                                    <span class="badge bg-{{ $caracteristica->pivot->POSEE ? 'success' : 'danger' }}">
                                                        {{ $caracteristica->pivot->POSEE ? '✔' : '✘' }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <a href="{{ route('plan.edit', $plan->PLA_ID) }}" class="btn btn-warning btn-sm">Editar</a>
                                        <form action="{{ route('plan.destroy', $plan->PLA_ID) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este plan?')">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Habilitar tooltip para el botón de ayuda
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Filtro en vivo para la tabla de Vehículos
        document.getElementById('filtro').addEventListener('input', function() {
            const filtro = this.value.toLowerCase();
            const filas = document.querySelectorAll('table tbody tr');

            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                fila.style.display = textoFila.includes(filtro) ? '' : 'none';
            });
        });
    </script>
@endsection
