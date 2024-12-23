@extends('layout')

@section('Titulo', 'Gestión de Vehículos')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Lista de Vehículos</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('vehiculos.create') }}" class="btn btn-contador">Agregar Vehículo</a>
            <div class="input-group" style="max-width: 400px;">
                <input type="text" id="filtro" class="form-control" placeholder="Filtrar Vehículos...">
                <button class="btn btn-secondary" type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Puedes buscar por Tipo, Placa, Estado, etc.">
                    ?
                </button>
            </div>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Placa</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vehiculos as $vehiculo)
                    <tr>
                        <td>{{ $vehiculo->VEH_ID }}</td>
                        <td>{{ $vehiculo->TIPO }}</td>
                        <td>{{ $vehiculo->PLACA }}</td>
                        <td>{{ $vehiculo->ESTADO }}</td>
                        <td>
                            <a href="{{ route('vehiculos.edit', $vehiculo->VEH_ID) }}"
                                class="btn btn-contador btn-sm">Editar</a>
                            <form action="{{ route('vehiculos.destroy', $vehiculo->VEH_ID) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('¿Está seguro de eliminar este vehículo?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
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
