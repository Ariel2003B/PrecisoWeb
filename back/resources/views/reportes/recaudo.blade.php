@extends('layout')
@section('Titulo', 'Recaudo de la Flota')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Recaudo de la Flota</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('reportes.index') }}">Hojas de Trabajo</a></li>
                        <li class="current">Recaudo</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section pt-3">
            <div class="container-fluid px-4">
                <form method="GET" action="{{ route('recaudo.index') }}" class="row g-2 align-items-end mb-3">
                    <div class="col-md-3">
                        <label class="form-label mb-0 small">Fecha Desde</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                               value="{{ request('fecha_inicio', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-0 small">Fecha Hasta</label>
                        <input type="date" name="fecha_fin" class="form-control form-control-sm"
                               value="{{ request('fecha_fin', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-0 small">Ruta</label>
                        <select name="ruta" class="form-select form-select-sm">
                            <option value="">Todas las rutas</option>
                            @foreach ($rutas as $ruta)
                                <option value="{{ $ruta->id_ruta }}" {{ request('ruta') == $ruta->id_ruta ? 'selected' : '' }}>
                                    {{ $ruta->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm">Consultar</button>
                        <a href="{{ route('recaudo.excel') }}" class="btn btn-success btn-sm">Excel</a>
                        <a href="{{ route('recaudo.pdf') }}" class="btn btn-danger btn-sm">PDF</a>
                    </div>
                </form>

                @if (isset($produccionPorUnidad))
                    @php
                        $hayTickets = $ticketTipos->count() > 0;
                        $totalA = 0; // Producción Conductor
                        $totalB = 0; // Producción Tickets ($)
                        $totalC = 0; // Tickets Físicos (cantidad)
                        $totalD = 0; // Contador Pasajeros
                        $totalesPorTipo = [];
                        foreach ($ticketTipos as $tt) {
                            $totalesPorTipo[$tt->id] = ['cantidad' => 0, 'valor' => 0];
                        }
                        $rowIdx = 0;
                    @endphp
                    <div class="table-responsive">
                        <table id="tablaRecaudo" class="table table-bordered table-sm text-center align-middle" style="font-size: 0.8rem;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Vueltas</th>
                                    <th>Unidad</th>
                                    <th>Prod. Conductor</th>
                                    @if ($hayTickets)
                                        <th>Prod. Tickets</th>
                                        <th>Tickets Físicos</th>
                                    @endif
                                    <th>Cont. Pasajeros</th>
                                    @if ($hayTickets)
                                        <th>Dif. Pasajeros</th>
                                        <th>Dif. Dinero</th>
                                        <th>Tarifa Promedio</th>
                                    @endif
                                </tr>
                                <tr class="filtros">
                                    <th><input type="text" placeholder="Filtrar..."></th>
                                    <th><input type="text" placeholder="Filtrar..."></th>
                                    <th><input type="text" placeholder="Filtrar..."></th>
                                    @if ($hayTickets)
                                        <th><input type="text" placeholder="Filtrar..."></th>
                                        <th><input type="text" placeholder="Filtrar..."></th>
                                    @endif
                                    <th><input type="text" placeholder="Filtrar..."></th>
                                    @if ($hayTickets)
                                        <th><input type="text" placeholder="Filtrar..."></th>
                                        <th><input type="text" placeholder="Filtrar..."></th>
                                        <th><input type="text" placeholder="Filtrar..."></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produccionPorUnidad as $unidad => $datos)
                                    @php
                                        $rowIdx++;
                                        $a = $datos['total_produccion'];
                                        $c = 0; $b = 0;
                                        foreach ($datos['tickets_por_tipo'] ?? [] as $tipoId => $info) {
                                            $c += $info['cantidad'];
                                            $b += $info['valor'];
                                            if (isset($totalesPorTipo[$tipoId])) {
                                                $totalesPorTipo[$tipoId]['cantidad'] += $info['cantidad'];
                                                $totalesPorTipo[$tipoId]['valor'] += $info['valor'];
                                            }
                                        }
                                        $d = $datos['pasajeros_wialon'];
                                        $difPas = $d - $c;
                                        $difDin = $b - $a;
                                        $tarifa = $d > 0 ? $a / $d : 0;

                                        $totalA += $a;
                                        $totalB += $b;
                                        $totalC += $c;
                                        $totalD += $d;
                                    @endphp
                                    <tr>
                                        <td>{{ $datos['total_vueltas'] }}</td>
                                        <td class="text-start">{{ $unidad }}</td>
                                        <td>${{ number_format($a, 2) }}</td>
                                        @if ($hayTickets)
                                            <td>${{ number_format($b, 2) }}</td>
                                            <td>
                                                <strong>{{ $c }}</strong>
                                                @if ($c > 0)
                                                    <a href="#" class="text-primary ms-1" style="font-size: 0.65rem;"
                                                       data-bs-toggle="collapse" data-bs-target="#det{{ $rowIdx }}">
                                                        detalle
                                                    </a>
                                                @endif
                                            </td>
                                        @endif
                                        <td>{{ $d }}</td>
                                        @if ($hayTickets)
                                            <td class="fw-bold {{ $difPas == 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $difPas > 0 ? '+' : '' }}{{ $difPas }}
                                            </td>
                                            <td class="fw-bold {{ $difDin <= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $difDin > 0 ? '+' : '' }}${{ number_format($difDin, 2) }}
                                            </td>
                                            <td>
                                                @if ($d > 0)
                                                    ${{ number_format($tarifa, 2) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                    @if ($hayTickets && $c > 0)
                                        <tr class="collapse" id="det{{ $rowIdx }}">
                                            <td colspan="9" class="p-0">
                                                <table class="table table-sm mb-0 table-light" style="font-size: 0.75rem;">
                                                    <thead>
                                                        <tr>
                                                            <th>Tipo</th>
                                                            <th>Valor Unit.</th>
                                                            <th>Cantidad</th>
                                                            <th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($ticketTipos as $tt)
                                                            @php $info = $datos['tickets_por_tipo'][$tt->id] ?? ['cantidad' => 0, 'valor' => 0]; @endphp
                                                            @if ($info['cantidad'] > 0)
                                                                <tr>
                                                                    <td>{{ $tt->nombre }}</td>
                                                                    <td>${{ number_format($tt->valor, 2) }}</td>
                                                                    <td>{{ $info['cantidad'] }}</td>
                                                                    <td>${{ number_format($info['valor'], 2) }}</td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                @php
                                    $tDifPas = $totalD - $totalC;
                                    $tDifDin = $totalB - $totalA;
                                    $tTarifa = $totalD > 0 ? $totalA / $totalD : 0;
                                @endphp
                                <tr class="table-success fw-bold">
                                    <td>Total</td>
                                    <td></td>
                                    <td>${{ number_format($totalA, 2) }}</td>
                                    @if ($hayTickets)
                                        <td>${{ number_format($totalB, 2) }}</td>
                                        <td>{{ $totalC }}</td>
                                    @endif
                                    <td>{{ $totalD }}</td>
                                    @if ($hayTickets)
                                        <td class="{{ $tDifPas == 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $tDifPas > 0 ? '+' : '' }}{{ $tDifPas }}
                                        </td>
                                        <td class="{{ $tDifDin <= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $tDifDin > 0 ? '+' : '' }}${{ number_format($tDifDin, 2) }}
                                        </td>
                                        <td>${{ number_format($tTarifa, 2) }}</td>
                                    @endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    </main>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

    @if (isset($produccionPorUnidad))
    <script>
        $(document).ready(function () {
            var table = $('#tablaRecaudo').DataTable({
                order: [[2, 'desc']],
                paging: false,
                info: false,
                lengthChange: false,
                orderCellsTop: true,
                language: {
                    search: "Buscar:",
                    zeroRecords: "Sin resultados"
                }
            });

            // Filtros por columna
            $('#tablaRecaudo thead .filtros input').on('keyup change', function () {
                table.column($(this).parent().index())
                    .search(this.value)
                    .draw();
            });
        });
    </script>
    <style>
        #tablaRecaudo thead .filtros input {
            width: 100%;
            font-size: 0.7rem;
            padding: 2px 4px;
        }
        .dataTables_filter { margin-bottom: 8px; }
    </style>
    @endif
@endsection
