@extends('layout')

@section('Titulo', 'Listado de Empresas')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Lista de Empresas</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current">Empresas</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="{{ route('empresa.create') }}" class="btn btn-primary">Crear Empresa</a>
                    <div class="input-group" style="max-width: 400px;">
                        <input type="text" id="filtro" class="form-control" placeholder="Filtrar Empresas...">
                        <button class="btn btn-secondary" type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Puedes buscar por Nombre, RUC, Correo, Dirección o Estado.">
                            ?
                        </button>
                    </div>
                </div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>RUC</th>
                            <th>Dirección</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $empresa)
                            <tr>
                                <td>{{ $empresa->EMP_ID }}</td>
                                <td>{{ $empresa->NOMBRE }}</td>
                                <td>{{ $empresa->RUC }}</td>
                                <td>{{ $empresa->DIRECCION }}</td>
                                <td>{{ $empresa->CORREO }}</td>
                                <td>{{ $empresa->TELEFONO }}</td>
                                <td>{{ $empresa->ESTADO }}</td>
                                <td>
                                    <a href="{{ route('empresa.edit', $empresa->EMP_ID) }}"
                                        class="btn btn-primary btn-sm">Editar</a>
                                    <a class="btn btn-sm btn-outline-primary"
                                        href="{{ route('empresa.stops.form', $empresa->EMP_ID) }}">
                                        Configurar geocercas
                                    </a>

                                    <form action="{{ route('empresa.destroy', $empresa->EMP_ID) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Está seguro de eliminar esta empresa?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

        // Filtro en vivo para la tabla de Empresas
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

@section('jsCode', 'js/scriptNavBar.js')
