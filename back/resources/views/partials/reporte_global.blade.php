<table class="table table-bordered text-center align-middle">
    <thead class="table-dark">
        <tr>
            <th>Unidad</th>
            <th>Total Producción ($)</th>
            <th>Total Vueltas</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($produccionPorUnidad as $unidad => $datos)
            <tr>
                <td>{{ $unidad }}</td>
                <td>${{ number_format($datos['total_produccion'], 2) }}</td>
                <td>{{ $datos['total_vueltas'] }}</td>
            </tr>
        @endforeach
        <tr class="table-success">
            <th>Total Global</th>
            <th>${{ number_format($totalGlobal, 2) }}</th>
            <th>{{ $totalVueltasGlobal }}</th>
        </tr>
    </tbody>
</table>
