<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Sanciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* Reducir el tamaño de fuente */
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            text-align: center;
            padding: 4px; /* Reducir padding */
            font-size: 10px; /* Reducir el tamaño de fuente */
        }
        th {
            background-color: #f2f2f2;
        }
        img {
            max-width: 100px; /* Reducir tamaño del logo */
            float: right;
        }
        table {
            page-break-inside: auto; /* Permitir división de tabla entre páginas */
        }
        tr {
            page-break-inside: avoid; /* Evitar cortar filas */
            page-break-after: auto;
        }
        th {
            position: sticky;
            top: 0; /* Encabezado fijo */
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Reporte de Sanciones</h1>
    <img src="{{ asset('images/logo_empresa.png') }}" alt="Logo Empresa">

    <table>
        <thead>
            <tr>
                <th>Unidad</th>
                <th>Placa</th>
                @foreach ($geocercas as $geocerca)
                    <th style="width: 50px; white-space: nowrap;">{{ $geocerca }}</th> <!-- Ajustar ancho -->
                @endforeach
                <th>Total</th>
                <th>Valor Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datosSeleccionados as $dato)
                <tr>
                    <td>{{ $dato['unidad'] }}</td>
                    <td>{{ $dato['placa'] }}</td>
                    @foreach ($geocercas as $geocerca)
                        <td>{{ $dato['geocercas'][$geocerca] ?? 0 }}</td>
                    @endforeach
                    <td>{{ $dato['total'] }}</td>
                    <td>{{ $dato['valor_total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
