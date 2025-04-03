@extends('layout')

@section('Titulo', 'Listado de Usuarios')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Lista de Usuarios</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current">Usuarios</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="{{ route('usuario.create') }}" class="btn btn-primary">Crear Usuario</a>
                    <div class="input-group" style="max-width: 400px;">
                        <input type="text" id="filtro" class="form-control" placeholder="Filtrar Usuarios...">
                        <button class="btn btn-secondary" type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Puedes buscar por Nombre, Apellido, Correo, Perfil o Estado.">
                            ?
                        </button>
                    </div>
                </div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Correo</th>
                            <th>Telefono</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->USU_ID }}</td>
                                <td>{{ $usuario->NOMBRE }}</td>
                                <td>{{ $usuario->APELLIDO }}</td>
                                <td>{{ $usuario->CORREO }}</td>
                                <td>{{ $usuario->TELEFONO }}</td>
                                <td>{{ $usuario->CEDULA }}</td>
                                <td>
                                    <a href="{{ route('usuario.edit', $usuario->USU_ID) }}"
                                        class="btn btn-primary btn-sm">Editar</a>
                                    <form action="{{ route('usuario.destroy', $usuario->USU_ID) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Está seguro de eliminar este usuario?')">Eliminar</button>
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
@section('jsCode', 'js/scriptNavBar.js')
