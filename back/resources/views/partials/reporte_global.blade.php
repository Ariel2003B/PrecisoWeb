<table class="table table-bordered text-center align-middle">
    <thead class="table-dark">
        <tr>
            <th>Unidad</th>
            <th>Total Producción ($)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($produccionPorUnidad as $unidad => $total)
            <tr>
                <td>{{ $unidad }}</td>
                <td>${{ number_format($total, 2) }}</td>
            </tr>
        @endforeach
        <tr class="table-success">
            <th>Total Global</th>
            <th>${{ number_format($totalGlobal, 2) }}</th>
        </tr>
    </tbody>
</table>
