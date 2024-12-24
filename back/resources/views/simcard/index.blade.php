@extends('layout')

@section('Titulo', 'Gestión de SIM Cards')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Lista de SIM Cards</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('simcards.create') }}" class="btn btn-contador">Agregar SIM Card</a>
            <form action="{{ route('simcards.index') }}" method="GET" class="input-group" style="max-width: 400px;">
                <input type="text" name="search" id="filtro" class="form-control" placeholder="Filtrar SIM Cards..."
                    value="{{ request('search') }}">
                <button class="btn btn-contador" type="submit" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Puedes buscar por cualquier dato visible en la tabla, como Número, Propietario, Plan, ICC o Vehículo.">
                    Buscar
                </button>
            </form>
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
                    <th>Grupo</th>
                    <th>Asignacion</th>
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
                        <td>{{ $simcard->v_e_h_i_c_u_l_o->TIPO ?? 'Sin Asignar' }}</td>
                        <td>{{ $simcard->v_e_h_i_c_u_l_o->PLACA ?? 'Sin Asignar' }}</td>
                        <td>{{ $simcard->ESTADO }}</td>
                        <td>
                            <a href="{{ route('simcards.edit', $simcard->ID_SIM) }}"
                                class="btn btn-contador btn-sm">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Paginación de SIM Cards" class="shadow-sm p-3 mb-5 bg-body rounded">
                {{ $simcards->appends(request()->query())->links() }}
            </nav>
        </div>
    </section>

    <script>
        // Habilitar tooltip para el botón de ayuda
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
