<!DOCTYPE html>
<html>

<head>
    <title>Reporte de Recaudo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #004080;
            color: white;
            font-size: 20px;
            border-radius: 5px;
        }

        footer {
            text-align: center;
            font-size: 10px;
            margin-top: 20px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #0073e6;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e0e0e0;
        }

        .total-row {
            background-color: #d4edda;
            font-weight: bold;
            color: #155724;
        }

        h1,
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <header>
        Reporte de Recaudo
    </header>

    <h2>Resumen de Producción del dia {{ $datos[0]['fecha'] ?? 'No disponible' }}</h2>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Unidad (Placa - Habilitación)</th>
                <th>Ruta</th>
                <th>Tipo Día</th>
                <th>Vueltas</th>
                <th>Producción ($)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datos as $dato)
                <tr>
                    <td>{{ $dato['fecha'] }}</td>
                    <td>{{ $dato['unidad'] }}</td>
                    <td>{{ $dato['ruta'] }}</td>
                    <td>{{ $dato['tipo_dia'] }}</td>
                    <td>{{ $dato['vueltas'] }}</td>
                    <td>${{ number_format($dato['produccion'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5">Total Producción</td>
                <td>${{ number_format($totalGlobal, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <footer>
        Reporte generado automáticamente el {{ now()->format('d/m/Y H:i') }}
    </footer>

</body>

</html>
