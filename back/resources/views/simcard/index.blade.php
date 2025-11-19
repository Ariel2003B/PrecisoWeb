@php
    use Illuminate\Support\Str;
@endphp
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



                <div class="filtros-simcards-container mb-3">
                    <a href="{{ route('simcards.create') }}" class="btn btn-success mt-2">Agregar SIM Card</a>
                    @if (Auth::user()->permisos->contains('DESCRIPCION', 'WIALON DATA'))
                        {{-- <button class="btn btn-warning mt-2" id="actualizar-wialon" onclick="actualizarWialon()">
                            <i class="fas fa-sync-alt"></i> Actualizar Números en Wialon
                        </button>
                        <span id="cargando-texto" style="display:none; color: #005298;">Actualizando... Por favor,
                            espera.</span> --}}
                        <button class="btn btn-info mt-2" id="actualizar-simcards-wialon"
                            onclick="actualizarSimCardsDesdeWialon()" style="color: white">
                            <i class="fas fa-sync-alt"></i> Sincronizar datos
                        </button>
                        <span id="cargando-wialon" style="display:none; color: #005298;">Espera...</span>
                    @endif

                    <a class="btn btn-primary mt-2" href="{{ route('simcards.exportExcel') }}">Descargar reporte</a>


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
                        <select name="pago_estado" id="pagoEstado" class="filtros-simcards-select">
                            <option value="">Todos los pagos</option>
                            <option value="AL_DIA" {{ request('pago_estado') == 'AL_DIA' ? 'selected' : '' }}>Al día
                            </option>
                            <option value="PROXIMO" {{ request('pago_estado') == 'PROXIMO' ? 'selected' : '' }}>Próximo a
                                vencer</option>
                            <option value="VENCIDO" {{ request('pago_estado') == 'VENCIDO' ? 'selected' : '' }}>Vencido
                            </option>
                        </select>


                        <button class="btn btn btn-primary mt-2" type="submit">Buscar</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">N</th>
                                <th scope="col">Plataforma</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Cedula</th>
                                <th scope="col">Numero / Login</th>
                                <th scope="col">Equipo</th>
                                <th scope="col">Asignación</th>
                                <th scope="col">Pagos</th>
                                <th scope="col">Estado</th>
                                <th scope="col" class="text-nowrap" style="width: 1%; min-width: 210px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $secuencial = $simcards->firstItem();
                            @endphp
                            @foreach ($simcards as $simcard)
                                @php $p = $simcard->pagos_estado; @endphp

                                <tr data-pago-estado="{{ $p['estado'] }}" {{-- AL_DIA | PROXIMO | VENCIDO --}}
                                    data-pago-fuente="{{ $p['fuente'] ?? '-' }}"> {{-- Cuota | Servicio | - --}}
                                    <td>{{ $secuencial++ }}</td>
                                    <td>{{ $simcard->PLATAFORMA }}</td>
                                    <td>{{ $simcard->cliente_nombre }}</td>
                                    <td>{{ $simcard->cliente_cedula }}</td>
                                    <td>{{ $simcard->NUMEROTELEFONO }}</td>
                                    <td>{{ $simcard->EQUIPO }}</td>

                                    <td>
                                        <span class="badge bg-info">{{ $simcard->ASIGNACION ?? 'Sin Asignar' }}</span>
                                    </td>
                                    <td>
                                        @php $p = $simcard->pagos_estado; @endphp

                                        <span
                                            class="badge
                                                @if ($p['color'] === 'danger') bg-danger
                                                @elseif($p['color'] === 'warning') bg-warning text-dark
                                                @else bg-success @endif">
                                            {{ $p['estado'] === 'PROXIMO' ? 'PRÓXIMO A VENCER' : $p['estado'] }}
                                        </span>

                                        {{-- Detalle de qué está vencido / próximo --}}
                                        @if ($p['estado'] !== 'AL_DIA' && $p['resumen'] !== '-')
                                            <small class="text-muted d-block">
                                                {{ $p['resumen'] }}
                                                @if ($p['estado_servicio'] === $p['estado'] && $p['fecha_servicio'])
                                                    (Serv: {{ $p['fecha_servicio'] }})
                                                @endif
                                                @if ($p['estado_cuota'] === $p['estado'] && $p['fecha_cuota'])
                                                    (Cuota: {{ $p['fecha_cuota'] }})
                                                @endif
                                            </small>
                                        @endif
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

                                    <td class="table-actions text-nowrap">
                                        @if (Auth::check())
                                            @if (!Auth::user()->p_e_r_f_i_l->p_e_r_m_i_s_o_s->contains('DESCRIPCION', 'LECTURA'))
                                                {{-- Botonera para >= sm --}}
                                                <div class="d-none d-sm-inline-flex align-items-center gap-1">
                                                    <a href="{{ route('simcards.edit', $simcard->ID_SIM) }}"
                                                        class="btn btn-outline-primary btn-action" data-bs-toggle="tooltip"
                                                        title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                        <span class="d-none d-md-inline">Editar</span>
                                                    </a>

                                                    <a href="{{ route('simcards.contrato', $simcard->ID_SIM) }}"
                                                        class="btn btn-outline-primary btn-action"
                                                        data-bs-toggle="tooltip" title="Contrato">
                                                        <i class="fas fa-file-contract"></i>
                                                        <span class="d-none d-md-inline">Contrato</span>
                                                    </a>

                                                    <button type="button" class="btn btn-outline-secondary btn-action"
                                                        data-bs-toggle="tooltip" title="Información"
                                                        onclick="verInfoSim({{ $simcard->ID_SIM }})">
                                                        <i class="bi bi-info-circle"></i>
                                                        <span class="d-none d-md-inline">Info</span>
                                                    </button>
                                                </div>

                                                {{-- Dropdown compacto para < sm --}}
                                                <div class="dropdown d-inline-block d-sm-none">
                                                    <button class="btn btn-outline-primary btn-action dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-h"></i> Acciones
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('simcards.edit', $simcard->ID_SIM) }}">
                                                                <i class="fas fa-edit me-2"></i> Editar
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('simcards.contrato', $simcard->ID_SIM) }}">
                                                                <i class="fas fa-file-contract me-2"></i> Contrato
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" type="button"
                                                                onclick="verInfoSim({{ $simcard->ID_SIM }})">
                                                                <i class="bi bi-info-circle me-2"></i> Info
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
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

                    <div class="descargar-plantilla-container">
                        <p>¿No tienes la plantilla? <a href="{{ route('simcards.template') }}"
                                class="btn-descargar">DESCARGAR
                                PLANTILLA</a></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>

            </div>
        </div>

    </div>
    <div class="modal fade" id="modalSimInfo" tabindex="-1" aria-labelledby="modalSimInfoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="modalSimInfoLabel">Información de la SIM</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="modalSimInfoBody">
                    <div class="text-center p-5">
                        <div class="spinner-border" role="status"></div>
                        <div class="mt-2">Cargando...</div>
                    </div>
                </div>
                <div class="modal-footer py-2">
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
            cargandoTexto.style.display = "none";

            // Hacer la petición AJAX a Laravel
            fetch("{{ route('simcards.updateWialonPhones') }}", {
                    method: "GET"
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message); // Muestra el mensaje de éxito o error
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Sincronizar datos';
                    cargandoTexto.style.display = "none";
                })
                .catch(error => {
                    console.error("Error en la actualización:", error);
                    alert("Hubo un error en la actualización.");
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Sincronizar datos';
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
            cargandoTexto.style.display = "none";

            // Hacer la petición AJAX a Laravel
            fetch("{{ route('simcards.updateSimCardFromWialon') }}", {
                    method: "GET"
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message); // Muestra el mensaje de éxito o error
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Sincronizar datos';
                    cargandoTexto.style.display = "none";
                })
                .catch(error => {
                    console.error("Error en la actualización:", error);
                    alert("Hubo un error en la actualización.");
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Sincronizar datos';
                    cargandoTexto.style.display = "none";
                });
        }
    </script>
    <script>
        function verInfoSim(id) {
            const modalEl = document.getElementById('modalSimInfo');
            const bodyEl = document.getElementById('modalSimInfoBody');

            // Limpia y muestra "cargando"
            bodyEl.innerHTML = `
        <div class="text-center p-5">
            <div class="spinner-border" role="status"></div>
            <div class="mt-2">Cargando...</div>
        </div>`;

            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            // Petición AJAX (GET) que trae el HTML del parcial
            fetch(`{{ url('/simcards') }}/${id}/info`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(resp => {
                    if (!resp.ok) throw new Error('Error HTTP ' + resp.status);
                    return resp.text();
                })
                .then(html => {
                    bodyEl.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    bodyEl.innerHTML = `
                <div class="alert alert-danger">
                    No se pudo cargar la información. Intenta nuevamente.
                </div>`;
                });
        }
    </script>
    {{-- Modal visor genérico (img / pdf) --}}
    <div class="modal fade" id="viewerModal" tabindex="-1" aria-labelledby="viewerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="viewerModalLabel">Vista de documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="viewerBody">
                    <div class="text-center p-5">
                        <div class="spinner-border" role="status"></div>
                        <div class="mt-2">Cargando…</div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <a id="viewerOpenNew" href="#" target="_blank" class="btn btn-outline-secondary">Abrir en
                        pestaña</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openViewer(src, type) {
            const body = document.getElementById('viewerBody');
            const a = document.getElementById('viewerOpenNew');
            body.innerHTML = `
      <div class="text-center p-5">
        <div class="spinner-border" role="status"></div>
        <div class="mt-2">Cargando…</div>
      </div>`;
            a.href = src || '#';

            const modal = new bootstrap.Modal(document.getElementById('viewerModal'));
            modal.show();

            // Render según tipo
            setTimeout(() => {
                if (!src) {
                    body.innerHTML = `<div class="alert alert-danger mb-0">No hay archivo para mostrar.</div>`;
                    a.classList.add('disabled');
                    return;
                }
                a.classList.remove('disabled');

                if (type === 'image') {
                    body.innerHTML = `<img src="${src}" class="img-fluid rounded shadow-sm" alt="Documento">`;
                } else if (type === 'pdf') {
                    body.innerHTML = `
          <div class="ratio ratio-16x9">
            <iframe src="${src}" title="PDF" frameborder="0"></iframe>
          </div>`;
                } else {
                    body.innerHTML = `
          <div class="alert alert-info">
            Formato no previsualizable. Puedes abrirlo en una pestaña nueva.
          </div>`;
                }
            }, 150);
        }
    </script>

    <style>
        /* Miniaturas compactas y botones consistentes */
        .thumb-doc {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: .35rem;
            border: 1px solid rgba(0, 0, 0, .075);
        }

        .btn-viewer {
            padding: .2rem .5rem;
            font-size: .82rem;
            border-radius: .35rem;
        }
    </style>


@endsection
