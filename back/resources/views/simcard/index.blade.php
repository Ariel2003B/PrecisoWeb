@extends('layout')

@section('Titulo', 'Gestión de SIM Cards')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Lista de SIM Cards</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <h4>Errores durante la carga:</h4>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('simcards.bulkUpload') }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            <div class="d-flex align-items-center">
                <label for="csv_file" class="form-label me-2">Carga masiva:</label>
                <i class="fas fa-info-circle text-primary ms-2" data-bs-toggle="modal" data-bs-target="#infoModal"
                    style="cursor: pointer;"></i>
            </div>
            <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
            <button type="submit" class="btn btn-success mt-2">Cargar Datos</button>
        </form>
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
<div class="table-responsive">
    <table class="table table-bordered table-hover table-striped align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th scope="col">Propietario</th>
                <th scope="col">Cuenta</th>
                <th scope="col">Plan</th>
                <th scope="col">Código Plan</th>
                <th scope="col">ICC</th>
                <th scope="col">Número</th>
                <th scope="col">Grupo</th>
                <th scope="col">Asignación</th>
                <th scope="col">Equipo</th>
                <th scope="col">Estado</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($simcards as $simcard)
                <tr>
                    <td>{{ $simcard->PROPIETARIO }}</td>
                    <td>{{ $simcard->CUENTA }}</td>
                    <td>{{ $simcard->PLAN }}</td>
                    <td>{{ $simcard->TIPOPLAN }}</td>
                    <td>{{ $simcard->ICC }}</td>
                    <td>{{ $simcard->NUMEROTELEFONO }}</td>
                    <td>
                        <span class="badge bg-secondary">
                            {{ $simcard->GRUPO ?? 'Sin Asignar' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-info">
                            {{ $simcard->ASIGNACION ?? 'Sin Asignar' }}
                        </span>
                    </td>
                    <td>{{$simcard->EQUIPO}}</td>
                    <td>
                        @if ($simcard->ESTADO === 'ACTIVA')
                            <span class="badge bg-success">Activa</span>
                        @elseif ($simcard->ESTADO === 'INACTIVA')
                            <span class="badge bg-danger">Inactiva</span>
                        @elseif ($simcard->ESTADO === 'LIBRE')
                            <span class="badge bg-warning">Libre</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('simcards.edit', $simcard->ID_SIM) }}" 
                            class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

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


    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Formato para carga masiva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Por favor, asegúrese de que el archivo CSV cumpla con el siguiente formato:</p>
                    <ul>
                        <li><b>Encabezado:</b> La primera fila debe contener los nombres de las columnas.</li>
                        <li><b>Columnas requeridas:</b> <code>PROPIETARIO, CUENTA, PLAN, TIPO PLAN, ICC, NUMERO TELEFONO,
                                TIPO VEHICULO, PLACA, ESTADO</code></li>
                        <li><b>Ejemplo:</b></li>
                    </ul>
                    <pre>
PROPIETARIO;CUENTA;PLAN;TIPO PLAN;ICC;NUMERO TELEFONO;TIPO VEHICULO;PLACA;ESTADO
PRECISOGPS S.A.S.;120013636;CLARO EMPRESA BAM 1.5;BP-9980;8959301001049890843;991906800;COMERCIALES;JQ049D;Activa
                </pre>
                    <p>Nota: Asegúrese de que las celdas no contengan comillas adicionales ni estén vacías para columnas
                        obligatorias.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
