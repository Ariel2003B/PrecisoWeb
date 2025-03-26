<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    {{-- <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table, th, td { border: 1px solid black; border-collapse: collapse; }
        th, td { padding: 3px; text-align: center; }
        .header { margin-bottom: 10px; }
        .no-border { border: none !important; }
        .left { text-align: left; }
        .bold { font-weight: bold; }
        .section-title { margin-top: 15px; margin-bottom: 5px; font-weight: bold; text-decoration: underline; }
    </style> --}}

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 3px;
            text-align: center;
        }

        .header {
            margin-bottom: 10px;
        }

        .no-border {
            border: none !important;
        }

        .left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .signature {
            margin-top: 40px;
            text-align: center;
        }

        /* Colores suaves para producción y gastos */
        .prod-table th {
            background-color: #E0F7FA;
        }

        .gastos-table th {
            background-color: #FFF3E0;
        }

        .total-row {
            background-color: #F1F8E9;
            font-weight: bold;
        }
    </style>

</head>

<body>
    <!-- Encabezado con logo a la izquierda -->
    <table width="100%" style="border: none; margin-bottom: 10px;">
        <tr>
            <!-- Columna izquierda: logo -->
            <td style="width: 25%; text-align: left; border: none;">
                <img src="http://precisogps.com/img/Precisogps.png" alt="Logo" width="150">
            </td>

            <!-- Columna central: títulos centrados visualmente -->
            <td style="width: 50%; text-align: center; border: none;">
                <h2 style="margin: 0;">HOJA DE TRABAJO</h2>
                <h3 style="margin: 0;">CÍA. TRANSMETROPOLI S.A.</h3>
            </td>

            <!-- Columna derecha vacía para compensar -->
            <td style="width: 25%; border: none;"></td>
        </tr>
    </table>



    <table width="100%" class="header">
        <tr>
            <td class="no-border bold">LABORABLE: {{ $hoja->tipo_dia === 'LABORABLE' ? 'X' : '' }}</td>
            <td class="no-border bold">FERIADO: {{ $hoja->tipo_dia === 'FERIADO' ? 'X' : '' }}</td>
            <td class="no-border bold">SÁBADO: {{ $hoja->tipo_dia === 'SABADO' ? 'X' : '' }}</td>
            <td class="no-border bold">DOMINGO: {{ $hoja->tipo_dia === 'DOMINGO' ? 'X' : '' }}</td>
            <td class="no-border bold">FECHA: {{ $hoja->fecha }}</td>
        </tr>
        <tr>
            <td colspan="3" class="no-border bold">RUTA: {{ $hoja->ruta->descripcion }}</td>
            <td colspan="2" class="no-border bold">UNIDAD No.: {{ $hoja->unidad->numero_habilitacion }}</td>
        </tr>
        <tr>
            <td colspan="3" class="no-border bold">CONDUCTOR: {{ $hoja->conductor->nombre }}</td>
            <td colspan="2" class="no-border bold">AYUDANTE: {{ $hoja->ayudante_nombre }}</td>
        </tr>
    </table>

    <table width="100%" class="prod-table">
        <tr>
            <!-- PRODUCCIÓN -->
            <td style="vertical-align: top;" width="60%">
                <table width="100%">
                    <thead>
                        <tr>
                            <th rowspan="2">No. de vuelta</th>
                            <th colspan="2">HORA</th>
                            <th colspan="2">VALOR</th>
                            <th rowspan="2">TOTAL POR VUELTA</th>
                        </tr>
                        <tr>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>B</th>
                            <th>S</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalProduccion = 0; @endphp
                        @for ($i = 1; $i <= 10; $i++)
                            @php
                                $prod = $hoja->producciones->firstWhere('nro_vuelta', $i);
                                $hora_b = $prod?->hora_bajada ?? '';
                                $hora_s = $prod?->hora_subida ?? '';
                                $valor_b = $prod?->valor_bajada ?? 0;
                                $valor_s = $prod?->valor_subida ?? 0;
                                $total = $valor_b + $valor_s;
                                $totalProduccion += $total;
                            @endphp
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $hora_b }}</td>
                                <td>{{ $hora_s }}</td>
                                <td>{{ number_format($valor_b, 2) }}</td>
                                <td>{{ number_format($valor_s, 2) }}</td>
                                <td>{{ $total > 0 ? number_format($total, 2) : '' }}</td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="5" class="total-row left">TOTAL PRODUCCIÓN</td>
                            <td class="total-row">{{ number_format($totalProduccion, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- GASTOS -->
            <td style="vertical-align: top;" width="40%">
                <table width="100%" class="gastos-table">
                    <thead>
                        <tr>
                            <th colspan="2" style="height: 36px;">GASTOS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalGastos = 0; @endphp
                        @foreach (['DIESEL', 'CONDUCTOR', 'AYUDANTE', 'ALIMENTACION', 'OTROS'] as $tipo)
                            @php
                                $valor = $hoja->gastos->where('tipo_gasto', $tipo)->sum('valor');
                                $totalGastos += $valor;
                            @endphp
                            <tr>
                                <td>{{ $tipo }}</td>
                                <td>{{ $valor > 0 ? number_format($valor, 2) : '' }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="total-row left">TOTAL GASTOS</td>
                            <td class="total-row">{{ number_format($totalGastos, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <div class="signature">
                    <p>______________________________</p>
                    <p>Firma del responsable</p>
                    <p><strong>{{ $user->NOMBRE }} {{ $user->APELLIDO }}</strong></p>
                </div>
                <br><br>
                <table width="100%">
                    <tr>
                        <td class="total-row">TOTAL A DEPOSITAR</td>
                        <td class="total-row">{{ number_format($totalProduccion - $totalGastos, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>


    {{-- @if ($hoja->gastos->whereIn('tipo_gasto', ['DIESEL', 'OTROS'])->whereNotNull('imagen')->count())
        <div style="page-break-before: always;"></div> <!-- Fuerza nueva página en el PDF -->

        <h3 style="text-align: center; margin-bottom: 20px;">ANEXOS</h3>

        @foreach ($hoja->gastos->whereIn('tipo_gasto', ['DIESEL', 'OTROS']) as $gasto)
            @if ($gasto->imagen)
                <div style="margin-bottom: 25px; text-align: center;">
                    <p class="bold">{{ $gasto->tipo_gasto }} - Foto</p>
                    
                    <img src="http://precisogps.com/back/storage/app/public/gastos/gasto_67e42863aacfc.png" alt="Imagen {{ $gasto->tipo_gasto }}"
                        style="max-width: 400px;">
                </div>
            @endif
        @endforeach
    @endif --}}

</body>

</html>
