<div class="table-responsive">
<table id="tablaProduccion" class="table table-bordered text-center align-middle table-sm" style="font-size: 0.75rem;">
    <thead class="table-dark">
        <tr>
            <th>Vueltas</th>
            <th>Unidad</th>
            <th>Producción ($)</th>
            @foreach ($ticketTipos as $tt)
                <th>{{ $tt->nombre }} (${{ number_format($tt->valor, 2) }})</th>
            @endforeach
            @if ($ticketTipos->count() > 0)
                <th>Total Tickets</th>
            @endif
            <th>Pasajeros Wialon</th>
            @if ($ticketTipos->count() > 0)
                <th>Diferencia</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @php
            $totalTicketsGlobal = 0;
            $totalPasajerosGlobal = 0;
            $totalesPorTipo = [];
            foreach ($ticketTipos as $tt) {
                $totalesPorTipo[$tt->id] = ['cantidad' => 0, 'valor' => 0];
            }
        @endphp
        @foreach ($produccionPorUnidad as $unidad => $datos)
            @php
                $totalTicketsUnidad = 0;
                foreach ($datos['tickets_por_tipo'] ?? [] as $tipoId => $info) {
                    $totalTicketsUnidad += $info['cantidad'];
                    if (isset($totalesPorTipo[$tipoId])) {
                        $totalesPorTipo[$tipoId]['cantidad'] += $info['cantidad'];
                        $totalesPorTipo[$tipoId]['valor'] += $info['valor'];
                    }
                }
                $totalTicketsGlobal += $totalTicketsUnidad;
                $totalPasajerosGlobal += $datos['pasajeros_wialon'];
                $diferencia = $datos['pasajeros_wialon'] - $totalTicketsUnidad;
            @endphp
            <tr>
                <td>{{ $datos['total_vueltas'] }}</td>
                <td>{{ $unidad }}</td>
                <td>${{ number_format($datos['total_produccion'], 2) }}</td>
                @foreach ($ticketTipos as $tt)
                    @php
                        $info = $datos['tickets_por_tipo'][$tt->id] ?? ['cantidad' => 0, 'valor' => 0];
                    @endphp
                    <td>
                        {{ $info['cantidad'] }}
                        <small class="text-muted">(${{ number_format($info['valor'], 2) }})</small>
                    </td>
                @endforeach
                @if ($ticketTipos->count() > 0)
                    <td><strong>{{ $totalTicketsUnidad }}</strong></td>
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
            @foreach ($ticketTipos as $tt)
                <th>
                    {{ $totalesPorTipo[$tt->id]['cantidad'] }}
                    <small>(${{ number_format($totalesPorTipo[$tt->id]['valor'], 2) }})</small>
                </th>
            @endforeach
            @if ($ticketTipos->count() > 0)
                <th>{{ $totalTicketsGlobal }}</th>
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
