@extends('layout')

@section('Titulo', 'Listado de Unidades')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Listado de Unidades</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('unidades.index') }}">Unidades</a></li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section py-5">
            <div class="container">
                <a href="{{ route('unidades.create') }}" class="btn btn-primary mb-4">Crear Unidad</a>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="input-group mb-3" style="max-width: 400px;">
                    <input type="text" id="filtroUnidades" class="form-control" placeholder="Filtrar unidades...">
                    <button class="btn btn-primary" type="button" data-bs-toggle="tooltip"
                        title="Puedes buscar por placa, propietario, año o empresa.">
                        <i class="bi bi-search"></i>
                    </button>
                </div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Propietario</th>
                            <th>Año</th>
                            <th>Empresa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unidades as $unidad)
                            <tr>
                                <td>{{ $unidad->placa . ' (' . $unidad->numero_habilitacion . ')' }}</td>
                                <td>
                                    {{ ($unidad->usuario->NOMBRE ?? '') . ' ' . ($unidad->usuario->APELLIDO ?? 'No asignado') }}
                                </td>
                                <td>{{ $unidad->anio_fabricacion }}</td>
                                <td>
                                    {{ $unidad->usuario->empresa->NOMBRE ?? 'Sin Empresa' }}
                                </td>

                                <td>
                                    <a href="{{ route('unidades.edit', $unidad->id_unidad) }}"
                                        class="btn btn-sm btn-warning" style="color: white">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Habilitar tooltips Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Filtro en vivo
            document.getElementById('filtroUnidades').addEventListener('input', function() {
                const filtro = this.value.toLowerCase();
                const filas = document.querySelectorAll('table tbody tr');

                filas.forEach(fila => {
                    const textoFila = fila.textContent.toLowerCase();
                    fila.style.display = textoFila.includes(filtro) ? '' : 'none';
                });
            });
        });
    </script>

@endsection

@section('jsCode', 'js/scriptNavBar.js')
