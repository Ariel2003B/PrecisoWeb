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
                <a href="{{ route('recaudo.index') }}" class="btn btn-success">
                    Recaudo de la Flota
                </a>
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
                                            @if (Auth::user()->permisos->contains('DESCRIPCION', 'FISCALIZADOR CONTEO Y RECAUDO'))
                                                <a href="{{ route('reportes.create', $hoja->id_hoja) }}"
                                                    class="btn btn-primary btn-sm">Fiscalizador</a>
                                            @endif
                                            @if (!Auth::user()->permisos->contains('DESCRIPCION', 'FISCALIZADOR CONTEO Y RECAUDO'))
                                                <a href="{{ route('hoja.ver', $hoja->id_hoja) }}"
                                                    class="btn btn-success btn-sm">Visualizar</a>
                                                <a href="{{ url('/api/hojas-trabajo/' . ($hoja->id_hoja ?? 0) . '/generar-pdfWeb') }}"
                                                    class="btn btn-danger btn-sm" target="_blank">PDF</a>
                                                <a href="{{ route('reportes.create', $hoja->id_hoja) }}"
                                                    class="btn btn-primary btn-sm">Fiscalizador</a>
                                            @endif
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
        .btn-group .btn { margin-right: 5px; }
        .btn-group .btn:last-child { margin-right: 0; }
        .table-bordered th, .table-bordered td { padding: 6px; }
        .table th, .table td { padding-left: 5px; padding-right: 5px; }
        .table th:last-child, .table td:last-child { width: 1%; white-space: nowrap; }
    </style>





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
