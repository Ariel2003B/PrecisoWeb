<div class="table-responsive">
<table id="tablaProduccion" class="table table-bordered text-center align-middle table-sm" style="font-size: 0.75rem;">
    <thead class="table-dark">
        <tr>
            <th>Vueltas</th>
            <th>Unidad</th>
            <th>Producción ($)</th>
            @if ($ticketTipos->count() > 0)
                <th>Tickets</th>
                <th>$ Tickets</th>
            @endif
            <th>Contador de Pasajeros</th>
            @if ($ticketTipos->count() > 0)
                <th>Diferencia</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @php
            $totalTicketsGlobal = 0;
            $totalValorTicketsGlobal = 0;
            $totalPasajerosGlobal = 0;
            $totalesPorTipo = [];
            foreach ($ticketTipos as $tt) {
                $totalesPorTipo[$tt->id] = ['cantidad' => 0, 'valor' => 0];
            }
            $rowIdx = 0;
        @endphp
        @foreach ($produccionPorUnidad as $unidad => $datos)
            @php
                $rowIdx++;
                $totalTicketsUnidad = 0;
                $totalValorTicketsUnidad = 0;
                foreach ($datos['tickets_por_tipo'] ?? [] as $tipoId => $info) {
                    $totalTicketsUnidad += $info['cantidad'];
                    $totalValorTicketsUnidad += $info['valor'];
                    if (isset($totalesPorTipo[$tipoId])) {
                        $totalesPorTipo[$tipoId]['cantidad'] += $info['cantidad'];
                        $totalesPorTipo[$tipoId]['valor'] += $info['valor'];
                    }
                }
                $totalTicketsGlobal += $totalTicketsUnidad;
                $totalValorTicketsGlobal += $totalValorTicketsUnidad;
                $totalPasajerosGlobal += $datos['pasajeros_wialon'];
                $diferencia = $datos['pasajeros_wialon'] - $totalTicketsUnidad;
            @endphp
            <tr>
                <td>{{ $datos['total_vueltas'] }}</td>
                <td>{{ $unidad }}</td>
                <td>${{ number_format($datos['total_produccion'], 2) }}</td>
                @if ($ticketTipos->count() > 0)
                    <td>
                        <strong>{{ $totalTicketsUnidad }}</strong>
                        @if ($totalTicketsUnidad > 0)
                            <button type="button" class="btn btn-outline-primary btn-sm ms-1 py-0 px-1"
                                    style="font-size: 0.65rem;"
                                    data-bs-toggle="modal" data-bs-target="#modalTickets{{ $rowIdx }}">
                                Ver
                            </button>
                        @endif
                    </td>
                    <td>${{ number_format($totalValorTicketsUnidad, 2) }}</td>
                @endif
                <td>{{ $datos['pasajeros_wialon'] }}</td>
                @if ($ticketTipos->count() > 0)
                    <td class="{{ $diferencia > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                        {{ $diferencia }}
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="table-success">
            <th>Total</th>
            <th></th>
            <th>${{ number_format($totalGlobal, 2) }}</th>
            @if ($ticketTipos->count() > 0)
                <th>{{ $totalTicketsGlobal }}</th>
                <th>${{ number_format($totalValorTicketsGlobal, 2) }}</th>
            @endif
            <th>{{ $totalPasajerosGlobal }}</th>
            @if ($ticketTipos->count() > 0)
                <th class="{{ ($totalPasajerosGlobal - $totalTicketsGlobal) > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                    {{ $totalPasajerosGlobal - $totalTicketsGlobal }}
                </th>
            @endif
        </tr>
    </tfoot>
</table>
</div>

{{-- Modales de detalle de tickets por unidad --}}
@if ($ticketTipos->count() > 0)
    @php $modalIdx = 0; @endphp
    @foreach ($produccionPorUnidad as $unidad => $datos)
        @php $modalIdx++; @endphp
        <div class="modal fade" id="modalTickets{{ $modalIdx }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h6 class="modal-title">Detalle Tickets — {{ $unidad }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-3">
                        <table class="table table-sm table-bordered text-center mb-0" style="font-size: 0.8rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Valor Unit.</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ticketTipos as $tt)
                                    @php
                                        $info = $datos['tickets_por_tipo'][$tt->id] ?? ['cantidad' => 0, 'valor' => 0];
                                    @endphp
                                    <tr>
                                        <td class="text-start">{{ $tt->nombre }}</td>
                                        <td>${{ number_format($tt->valor, 2) }}</td>
                                        <td>{{ $info['cantidad'] }}</td>
                                        <td>${{ number_format($info['valor'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                @php
                                    $totalCant = 0;
                                    $totalVal = 0;
                                    foreach ($datos['tickets_por_tipo'] ?? [] as $info) {
                                        $totalCant += $info['cantidad'];
                                        $totalVal += $info['valor'];
                                    }
                                @endphp
                                <tr class="fw-bold">
                                    <td class="text-start">Total</td>
                                    <td></td>
                                    <td>{{ $totalCant }}</td>
                                    <td>${{ number_format($totalVal, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
