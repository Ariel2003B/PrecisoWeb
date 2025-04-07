<table class="table table-bordered text-center align-middle">
    <thead class="table-dark">
        <tr>
            <th>Vueltas</th>
            <th>Unidad</th>
            <th>Producción ($)</th>
            
        </tr>
    </thead>
    <tbody>
        <th>{{ $totalVueltasGlobal }}</th>
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
           
        </tr>
    </tbody>
</table>
