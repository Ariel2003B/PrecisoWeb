@extends('layout')

@section('Titulo', 'Gestión de SIM Cards')

@section('content')
    <main class="main">
        <!-- Page Title -->
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Lista de SIM Cards</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current">Simcards</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        {{-- <section class="container mt-5"> --}}
        <section class="section">
            <div class="container">
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
                <form action="{{ route('simcards.bulkUpload') }}" method="POST" enctype="multipart/form-data"
                    class="mb-4">
                    @csrf
                    <div class="d-flex align-items-center">
                        <label for="csv_file" class="form-label me-2">Carga masiva:</label>
                        <i class="fas fa-info-circle text-primary ms-2" data-bs-toggle="modal" data-bs-target="#infoModal"
                            style="cursor: pointer;"></i>
                    </div>
                    <div class="input-group">
                        <input type="file" name="csv_file" id="csv_file" accept=".csv" required class="form-control">
                        <button class="btn btn-success" type="submit">
                            <i class="bi bi-cloud-upload"></i> Cargar datos
                        </button>
                    </div>
                </form>


                <div class="descargar-plantilla-container">
                    <p>¿No tienes la plantilla? <a href="{{ route('simcards.template') }}" class="btn-descargar">DESCARGAR
                            PLANTILLA</a></p>
                </div>
                <div class="filtros-simcards-container mb-3">
                    <a href="{{ route('simcards.create') }}" class="btn btn-success mt-2">Agregar SIM Card</a>
                    @if (Auth::user()->p_e_r_f_i_l->p_e_r_m_i_s_o_s->contains('DESCRIPCION', 'WIALON DATA'))
                        <button class="btn btn-warning mt-2" id="actualizar-wialon" onclick="actualizarWialon()">
                            <i class="fas fa-sync-alt"></i> Actualizar Números en Wialon
                        </button>
                        <span id="cargando-texto" style="display:none; color: #005298;">Actualizando... Por favor, espera.</span>
                        <button class="btn btn-info mt-2" id="actualizar-simcards-wialon"
                            onclick="actualizarSimCardsDesdeWialon()">
                            <i class="fas fa-sync-alt"></i> Actualizar SIM Cards desde Wialon
                        </button>
                        <span id="cargando-wialon" style="display:none; color: #005298;">Actualizando... Por favor, espera.</span>
                    @endif

                    <a href="{{route('simcards.exportExcel')}}">Descargar reporte</a>
                
                    <form action="{{ route('simcards.index') }}" method="GET" class="filtros-simcards-form">
                        <input type="text" name="search" id="filtro" class="filtros-simcards-input"
                            placeholder="Busqueda avanzada..." value="{{ request('search') }}">
                        <select name="CUENTA" id="CUENTA" class="filtros-simcards-select">
                            <option value="">Todas las cuentas</option>
                            @foreach ($cuentas as $cuenta)
                                <option value="{{ $cuenta }}" {{ request('CUENTA') == $cuenta ? 'selected' : '' }}>
                                    {{ $cuenta }}</option>
                            @endforeach
                        </select>
                        <select name="PLAN" id="PLAN" class="filtros-simcards-select">
                            <option value="">Todos los planes</option>
                            @foreach ($planes as $plan)
                                <option value="{{ $plan }}" {{ request('PLAN') == $plan ? 'selected' : '' }}>
                                    {{ $plan }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn btn-primary mt-2" type="submit">Buscar</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">N</th>
                                <th scope="col">Cuenta</th>
                                <th scope="col">Plan</th>
                                <th scope="col">Código Plan</th>
                                <th scope="col">ICC</th>
                                <th scope="col">Número</th>
                                <th scope="col">Equipo</th>
                                <th scope="col">Imei</th>
                                <th scope="col">Grupo</th>
                                <th scope="col">Asignación</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $secuencial = $simcards->firstItem();
                            @endphp
                            @foreach ($simcards as $simcard)
                                <tr>
                                    <td>{{ $secuencial++ }}</td>
                                    <td>{{ $simcard->CUENTA }}</td>
                                    <td>{{ $simcard->PLAN }}</td>
                                    <td>{{ $simcard->TIPOPLAN }}</td>
                                    <td>{{ $simcard->ICC }}</td>
                                    <td>{{ $simcard->NUMEROTELEFONO }}</td>
                                    <td>{{ $simcard->EQUIPO }}</td>
                                    <td>{{ $simcard->IMEI }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $simcard->GRUPO ?? 'Sin Asignar' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $simcard->ASIGNACION ?? 'Sin Asignar' }}</span>
                                    </td>
                                    <td>
                                        @if ($simcard->ESTADO === 'ACTIVA')
                                            <span class="badge bg-success">Activa</span>
                                        @elseif ($simcard->ESTADO === 'ELIMINADA')
                                            <span class="badge bg-danger">Eliminada</span>
                                        @elseif ($simcard->ESTADO === 'LIBRE')
                                            <span class="badge bg-warning">Libre</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (Auth::check())
                                            @if (!Auth::user()->p_e_r_f_i_l->p_e_r_m_i_s_o_s->contains('DESCRIPCION', 'LECTURA'))
                                                <a href="{{ route('simcards.edit', $simcard->ID_SIM) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                            @endif
                                        @endif
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
            </div>
        </section>
    </main>
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
                        <li><b>Columnas requeridas:</b> <code>PROPIETARIO, CUENTA, PLAN, CODIGO PLAN, ICC, NUMERO TELEFONO,
                                GRUPO, ASIGNACION, ESTADO</code></li>
                        <li><b>Ejemplo:</b></li>
                    </ul>
                    <pre>
PROPIETARIO;CUENTA;PLAN;CODIGO PLAN;ICC;NUMERO TELEFONO;GRUPO;ASIGNACION;ESTADO
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

        // Habilitar tooltip para el botón de ayuda
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    <script>
        function actualizarWialon() {
            let btn = document.getElementById("actualizar-wialon");
            let cargandoTexto = document.getElementById("cargando-texto");

            // Deshabilitar el botón y mostrar "Cargando..."
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-sync fa-spin"></i> Actualizando...';
            cargandoTexto.style.display = "inline";

            // Hacer la petición AJAX a Laravel
            fetch("{{ route('simcards.updateWialonPhones') }}", {
                    method: "GET"
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message); // Muestra el mensaje de éxito o error
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Actualizar Números en Wialon';
                    cargandoTexto.style.display = "none";
                })
                .catch(error => {
                    console.error("Error en la actualización:", error);
                    alert("Hubo un error en la actualización.");
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Actualizar Números en Wialon';
                    cargandoTexto.style.display = "none";
                });
        }
    </script>
    {{-- para actualizar nuestra base --}}
    <script>
        function actualizarSimCardsDesdeWialon() {
            let btn = document.getElementById("actualizar-simcards-wialon");
            let cargandoTexto = document.getElementById("cargando-wialon");

            // Deshabilitar el botón y mostrar "Cargando..."
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-sync fa-spin"></i> Actualizando...';
            cargandoTexto.style.display = "inline";

            // Hacer la petición AJAX a Laravel
            fetch("{{ route('simcards.updateSimCardFromWialon') }}", {
                    method: "GET"
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message); // Muestra el mensaje de éxito o error
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Actualizar SIM Cards desde Wialon';
                    cargandoTexto.style.display = "none";
                })
                .catch(error => {
                    console.error("Error en la actualización:", error);
                    alert("Hubo un error en la actualización.");
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Actualizar SIM Cards desde Wialon';
                    cargandoTexto.style.display = "none";
                });
        }
    </script>

@endsection
