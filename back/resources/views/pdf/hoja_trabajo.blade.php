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
            <td colspan="2" class="no-border bold">UNIDAD No.: {{ $hoja->unidad->placa }}({{ $hoja->unidad->numero_habilitacion }})</td>
        </tr>
        <tr>
            <td colspan="3" class="no-border bold">CONDUCTOR: {{ $hoja->conductor->nombre }}</td>
            <td colspan="2" class="no-border bold">AYUDANTE: {{ $hoja->ayudante_nombre }}</td>
        </tr>
    </table>

    {{-- <table width="100%" class="prod-table">
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
        <tr>
            <td colspan="6" style="padding-top: 15px;">
                <table width="100%">
                    <thead>
                        <tr>
                            <th colspan="5" class="section-title" style="text-align:left;">DETALLE DE PRODUCCIÓN POR
                                USUARIO</th>
                        </tr>
                        <tr>
                            <th>No. Vuelta</th>
                            <th>Pasajes Completos</th>
                            <th>Pasajes Medios</th>
                            <th>Valor Vuelta</th>
                            <th>Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vueltasUsuario as $vu)
                            <tr>
                                <td>{{ $vu->nro_vuelta }}</td>
                                <td>{{ $vu->pasaje_completo }}</td>
                                <td>{{ $vu->pasaje_medio }}</td>
                                <td>{{ number_format($vu->valor_vuelta, 2) }}</td>
                                <td>{{ $vu->usuario->NOMBRE }} {{ $vu->usuario->APELLIDO }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>

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
    </table> --}}


    <table width="100%" class="prod-table">
        <tr>
            <!-- PRODUCCIÓN -->
            <td style="vertical-align: top;" width="60%">
                <table width="100%">
                    <thead>
                        <tr>
                            <th>No. de vuelta</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Total por Vuelta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalProduccion = 0; @endphp
                        @for ($i = 1; $i <= 10; $i++)
                            @php
                                $prod = $hoja->producciones->firstWhere('nro_vuelta', $i);
                                $hora_s = $prod?->hora_subida ?? '';
                                $hora_b = $prod?->hora_bajada ?? '';
                                $valor_vuelta = $prod?->valor_vuelta ?? 0;
                                $totalProduccion += $valor_vuelta;
                            @endphp
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $hora_s }}</td>
                                <td>{{ $hora_b }}</td>
                                <td>{{ $valor_vuelta > 0 ? number_format($valor_vuelta, 2) : '' }}</td>
                            </tr>
                        @endfor
                        <tr>
                            <td colspan="3" class="total-row left">TOTAL PRODUCCIÓN</td>
                            <td class="total-row">{{ number_format($totalProduccion, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- DETALLE ADICIONAL DEL USUARIO --}}
                {{-- DETALLE DE PRODUCCIÓN POR USUARIO --}}
                @if ($vueltasUsuario && $vueltasUsuario->count())
                    <br>
                    <table width="100%" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th colspan="4" class="section-title" style="text-align:left;">
                                    DETALLE DE PRODUCCIÓN POR USUARIO
                                </th>
                            </tr>
                            <tr>
                                <td colspan="4" class="bold" style="text-align:center; padding: 6px 0;">
                                    Registrado por: {{ $vueltasUsuario->first()->usuario->NOMBRE ?? '' }} {{ $vueltasUsuario->first()->usuario->APELLIDO ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <th>No. Vuelta</th>
                                <th>Pasajes Completos</th>
                                <th>Pasajes Medios</th>
                                <th>Valor Vuelta</th>
                           
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vueltasUsuario as $vu)
                                <tr>
                                    <td>{{ $vu->nro_vuelta }}</td>
                                    <td>{{ $vu->pasaje_completo }}</td>
                                    <td>{{ $vu->pasaje_medio }}</td>
                                    <td>{{ number_format($vu->valor_vuelta, 2) }}</td>
                                 
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td colspan="3" class="left">TOTAL PRODUCCIÓN USUARIO</td>
                                <td colspan="2">
                                    {{ number_format($vueltasUsuario->sum('valor_vuelta'), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                @endif

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

</body>

</html>
