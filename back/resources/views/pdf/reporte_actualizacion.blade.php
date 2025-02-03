<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Actualización</title>
</head>

<body>
    <h1>Reporte de Números Actualizados en Wialon</h1>
    <table border="1">
        <tr>
            <th>IMEI</th>
            <th>Nuevo Número</th>
        </tr>
        @foreach ($updatedSimcards as $sim)
            <tr>
                <td>{{ $sim['IMEI'] }}</td>
                <td>{{ $sim['Nuevo Número'] }}</td>
            </tr>
        @endforeach
    </table>
</body>

</html>
