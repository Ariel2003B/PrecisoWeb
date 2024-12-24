@extends('layout')

@section('Titulo', 'Gestión de SIM Cards')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Lista de SIM Cards</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('simcards.create') }}" class="btn btn-contador">Agregar SIM Card</a>
            <div class="input-group" style="max-width: 400px;">
                <input type="text" id="filtro" class="form-control" placeholder="Filtrar SIM Cards...">
                <button class="btn btn-contador" type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Puedes buscar por cualquier dato visible en la tabla, como Número, Propietario, Plan, ICC o Vehículo.">
                    ?
                </button>
            </div>
        </div>
        <form action="{{ route('simcards.bulkUpload') }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            <label for="csv_file" class="form-label">Migracion masiva:</label>
            <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
            <button type="submit" class="btn btn-success mt-2">Cargar Datos</button>
        </form>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>RUC</th>
                    <th>Propietario</th>
                    <th>Cuenta</th>
                    <th>Plan</th>
                    <th>Codigo plan</th>
                    <th>ICC</th>
                    <th>Número</th>
                    <th>Asignacion</th>
                    <th>Grupo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($simcards as $simcard)
                    <tr>
                        <td>{{ $simcard->RUC }}</td>
                        <td>{{ $simcard->PROPIETARIO }}</td>
                        <td>{{ $simcard->CUENTA }}</td>
                        <td>{{ $simcard->PLAN }}</td>
                        <td>{{ $simcard->TIPOPLAN }}</td>
                        <td>{{ $simcard->ICC }}</td>
                        <td>{{ $simcard->NUMEROTELEFONO }}</td>
                        <td>{{ $simcard->v_e_h_i_c_u_l_o->PLACA ?? 'Sin Asignar' }}</td>
                        <td>{{ $simcard->v_e_h_i_c_u_l_o->TIPO ?? 'Sin Asignar' }}</td>
                        <td>{{ $simcard->ESTADO }}</td>
                        <td>
                            <a href="{{ route('simcards.edit', $simcard->ID_SIM) }}"
                                class="btn btn-contador btn-sm">Editar</a>
                            <form action="{{ route('simcards.destroy', $simcard->ID_SIM) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
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
