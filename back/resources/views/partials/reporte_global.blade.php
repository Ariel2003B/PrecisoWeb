<table class="table table-bordered text-center align-middle">
    <thead class="table-dark">
        <tr>
            <th>Vueltas</th>
            <th>Unidad</th>
            <th>Producción ($)</th>
            
        </tr>
    </thead>
    <tbody>
        
        @foreach ($produccionPorUnidad as $unidad => $datos)
            <tr>
                 <td>{{ $datos['total_vueltas'] }}</td>
                <td>{{ $unidad }}</td>
                <td>${{ number_format($datos['total_produccion'], 2) }}</td>
               
            </tr>
        @endforeach
        <tr class="table-success">
            <th></th>
            <th>Total</th>
            <th>${{ number_format($totalGlobal, 2) }}</th>
        </tr>
        
    </tbody>
</table>
