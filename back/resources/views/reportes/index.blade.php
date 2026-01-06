@extends('layout')
@section('Titulo', 'Hojas de Trabajo')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Hojas de Trabajo</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Hojas de Trabajo</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <!-- Añadir botón para abrir el modal -->
            <div class="container mb-4">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#reporteGlobalModal">
                    Recaudo de la Flota
                </button>
            </div>

            <div class="container mb-4 border rounded p-3 bg-light">
                <h5>Generar Reporte PDF por Rango de Fechas</h5>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="fecha_inicio" class="form-label">Fecha Desde</label>
                        <input type="date" id="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_fin" class="form-label">Fecha Hasta</label>
                        <input type="date" id="fecha_fin" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-danger w-100" onclick="generarPDFRango()">
                            Generar PDF por Rango
                        </button>
                    </div>
                </div>
            </div>
            <script>
                function generarPDFRango() {
                    const inicio = document.getElementById('fecha_inicio').value;
                    const fin = document.getElementById('fecha_fin').value;

                    if (!inicio || !fin) {
                        alert('Por favor selecciona ambas fechas.');
                        return;
                    }

                    window.open(`/reporte-pdf-rango?fecha_inicio=${inicio}&fecha_fin=${fin}`, '_blank');
                }
            </script>

            <div class="container">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">

                    </div>
                    <div class="col-md-3">
                        <label>Unidad</label>
                        <input type="text" name="unidad" class="form-control"
                            placeholder="Buscar placa o habilitación..." value="{{ request('unidad') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Ruta</label>
                        <input type="text" name="ruta" class="form-control" placeholder="Buscar ruta..."
                            value="{{ request('ruta') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    </div>

                </form>
                <!-- Modal -->
                <div class="modal fade" id="reporteGlobalModal" tabindex="-1" aria-labelledby="reporteGlobalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="reporteGlobalLabel">Recaudo de la Flota</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="reporteGlobalForm">
                                    {{-- <div class="mb-3">
                                        <label for="fecha_reporte" class="form-label">Seleccionar Fecha</label>
                                        <input type="date" name="fecha" id="fecha_reporte" class="form-control"
                                            required>
                                    </div> --}}
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="fecha_inicio_reporte" class="form-label">Fecha Desde</label>
                                            <input type="date" id="fecha_inicio_reporte" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="fecha_fin_reporte" class="form-label">Fecha Hasta</label>
                                            <input type="date" id="fecha_fin_reporte" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="ruta_reporte" class="form-label">Seleccionar Ruta</label>
                                        <select name="ruta" id="ruta_reporte" class="form-control">
                                            <option value="">Todas las rutas</option>
                                            @foreach ($rutas as $ruta)
                                                <option value="{{ $ruta->id_ruta }}">{{ $ruta->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="button" class="btn btn-primary" onclick="generarReporte()">Visualizar
                                            Reporte</button>
                                        <button type="button" class="btn btn-success" onclick="generarExcel()">Generar
                                            Excel</button>
                                        <button type="button" class="btn btn-danger" onclick="generarPDF()">Generar
                                            PDF</button>
                                    </div>
                                </form>
                                <div id="reporteGlobalResultado" class="mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>


                <table class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Unidad</th>
                            <th>Ruta</th>
                            <th>Tipo Día</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedHojas = $hojas->groupBy('fecha');
                        @endphp

                        @forelse ($groupedHojas as $fecha => $hojasFecha)
                            @php
                                // Ordenar las hojas por número de habilitación dentro del mismo grupo de fecha
                                $hojasOrdenadas = $hojasFecha->sortBy(function ($hoja) {
                                    if ($hoja->unidad && $hoja->unidad->numero_habilitacion) {
                                        preg_match('/^(\d+)/', $hoja->unidad->numero_habilitacion, $matches);
                                        return $matches[1] ?? PHP_INT_MAX; // Si no encuentra número, lo manda al final
                                    }
                                    return PHP_INT_MAX;
                                });
                            @endphp

                            @foreach ($hojasOrdenadas as $hoja)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($hoja->fecha)->format('d/m/Y') }}</td>
                                    <td>({{ $hoja->unidad->numero_habilitacion ?? '-' }}) {{ $hoja->unidad->placa ?? '-' }}
                                    </td>
                                    <td>{{ $hoja->ruta->descripcion ?? '-' }}</td>
                                    <td>{{ $hoja->tipo_dia ?? '-' }}</td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Acciones">
                                            <a href="{{ route('reportes.create', $hoja->id_hoja) }}"
                                                class="btn btn-primary btn-sm">Fiscalizador</a>
                                            <a href="{{ route('hoja.ver', $hoja->id_hoja) }}"
                                                class="btn btn-success btn-sm">Visualizar</a>
                                            <a href="{{ url('/api/hojas-trabajo/' . ($hoja->id_hoja ?? 0) . '/generar-pdfWeb') }}"
                                                class="btn btn-danger btn-sm" target="_blank">PDF</a>
                                        </div>
                                    </td>


                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="5">No se encontraron hojas de trabajo con los filtros aplicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </section>
    </main>
    <style>
        .btn-group .btn {
            margin-right: 5px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        .table-bordered th,
        .table-bordered td {
            padding: 6px;
            /* Reducido desde 12px o más a 6px */
        }

        .table th,
        .table td {
            padding-left: 5px;
            padding-right: 5px;
        }

        /* Reducir el ancho de la columna de Acciones */
        .table th:last-child,
        .table td:last-child {
            width: 1%;
            white-space: nowrap;
        }
    </style>




    <script>
        function generarReporte() {
            const fechaInicio = document.getElementById('fecha_inicio_reporte').value;
            const fechaFin = document.getElementById('fecha_fin_reporte').value;
            const rutaId = document.getElementById('ruta_reporte').value;

            if (!fechaInicio || !fechaFin) {
                alert('Por favor selecciona el rango de fechas.');
                return;
            }

            fetch("{{ route('reporte.global') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin,
                        ruta: rutaId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('reporteGlobalResultado').innerHTML = data.html;


                    // Inicializar DataTables después de cargar la tabla
                    $('#tablaProduccion').DataTable({
                        "order": [
                            [2, "desc"]
                        ], // Ordenar inicialmente por Producción ($) de mayor a menor
                        "paging": false, // Desactiva la paginación
                        "searching": false, // Desactiva el campo de búsqueda
                        "info": false, // Desactiva la información de registros
                        "lengthChange": false, // Desactiva la opción de cambiar el número de registros mostrados
                        "ordering": true, // Mantiene la opción de ordenar por columnas
                        "language": {
                            "paginate": {
                                "next": "Siguiente",
                                "previous": "Anterior"
                            }
                        }
                    });
                })
                .catch(error => console.error('Error:', error));
        }

        function generarExcel() {
            window.location.href = "{{ route('reporte.global.excel') }}";
        }

        function generarPDF() {
            window.location.href = "{{ route('reporte.global.pdf') }}";
        }
    </script>

    <script>
        $(document).ready(function() {
            $('#tablaProduccion').DataTable({
                "order": [
                    [2, "desc"]
                ], // Ordenar inicialmente por Producción ($) de mayor a menor
                "paging": false,
                "searching": false,
                "info": false,
                "orderFixed": [
                    [2, "desc"]
                ], // Fijar el orden por Producción ($)
                "columnDefs": [{
                        "orderable": false,
                        "targets": '_all'
                    } // Evitar que el Total se ordene
                ],
                "drawCallback": function(settings) {
                    var api = this.api();
                    var totalRow = $('#tablaProduccion tfoot tr')
                        .detach(); // Remover la fila de total temporalmente
                    $('#tablaProduccion tbody').append(totalRow); // Reinsertar al final siempre
                }
            });
        });
    </script>

    <!-- CSS de DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- JS de DataTables -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

@endsection
