<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
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

        .total-row-depot {
            background-color: #f2d6d6;
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

            <!-- Columna derecha: logo de Metropoli -->
            <td style="width: 25%; text-align: right; border: none;">
                <img src="http://precisogps.com/img/clients/metropoli.png" alt="Logo Metropoli" width="100">
            </td>

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
            <td colspan="2" class="no-border bold">UNIDAD No.: {{ $hoja->unidad->placa }}
                ({{ $hoja->unidad->numero_habilitacion }})</td>
        </tr>

    </table>

    <table width="100%" class="prod-table">
        <tr>
            <!-- PRODUCCIÓN CHOFER -->
            <td width="60%" style="vertical-align: top;">
                <table width="100%">
                    <thead>
                        <tr>
                            <th colspan="4" class="section-title" style="text-align:left;">
                                DETALLE DE RECAUDO
                            </th>
                        </tr>
                        <tr>
                            <td colspan="4" class="bold" style="text-align:left; padding: 6px 0;">
                                Conductor: {{ $hoja->conductor->nombre }} Ayudante: {{ $hoja->ayudante_nombre }}
                            </td>
                        </tr>
                        <tr>
                            <th>No. de vuelta</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Total por Vuelta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalProduccion = 0; @endphp
                        @foreach ($hoja->producciones as $prod)
                            @php
                                $totalProduccion += $prod->valor_vuelta ?? 0;
                            @endphp
                            <tr>
                                <td>{{ $prod->nro_vuelta }}</td>
                                <td>{{ $prod->hora_subida }}</td>
                                <td>{{ $prod->hora_bajada }}</td>
                                <td>{{ $prod->valor_vuelta > 0 ? number_format($prod->valor_vuelta, 2) : '' }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" class="total-row left">TOTAL PRODUCCIÓN</td>
                            <td class="total-row">{{ number_format($totalProduccion, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- DETALLE USUARIO -->
            <td width="40%" style="vertical-align: top;">
                @if ($vueltasUsuario && $vueltasUsuario->count())
                    <table width="100%">
                        <thead>
                            <tr>
                                <th colspan="4" class="section-title" style="text-align:left;">
                                    DETALLE PRODUCCIÓN USUARIO
                                </th>
                            </tr>
                            <tr>
                                <td colspan="4" class="bold" style="text-align:center; padding: 6px 0;">
                                    Registrado por: {{ $vueltasUsuario->first()->usuario->NOMBRE ?? '' }}
                                    {{ $vueltasUsuario->first()->usuario->APELLIDO ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <th>No. Vuelta</th>
                                <th>Completos</th>
                                <th>Medios</th>
                                <th>Valor</th>
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
                                <td colspan="3" class="left">TOTAL USUARIO</td>
                                <td>{{ number_format($vueltasUsuario->sum('valor_vuelta'), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </td>
        </tr>
    </table>

    @if ($vueltasUsuario && $vueltasUsuario->count())
        @php
            $totalUsuario = $vueltasUsuario->sum('valor_vuelta');
            $diferencia = $totalProduccion - $totalUsuario;
        @endphp

        <div style="margin-top: 10px;">
            <table width="100%" style="border: none;">
                <tr>
                    <td class="no-border bold" style="text-align: left;">
                        @if ($diferencia > 0)
                            El chofer registró <span
                                style="color: rgb(0, 128, 255);">{{ number_format($diferencia, 2) }} dólares
                                más</span> que el conteo del usuario.
                        @elseif ($diferencia < 0)
                            El chofer registró <span style="color: red;">${{ number_format(abs($diferencia), 2) }}
                                dólares menos</span> que el conteo del usuario.
                        @else
                            El chofer y el usuario registraron la misma cantidad de dinero.
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif


    <!-- GASTOS Y TOTAL FINAL -->
    <table width="100%" class="gastos-table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th colspan="2">GASTOS</th>
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
            <tr>
                <td class="total-row-depot left">TOTAL A DEPOSITAR</td>
                <td class="total-row-depot">{{ number_format($totalProduccion - $totalGastos, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @if ($imagenDiesel || $imagenOtros)
        <div style="page-break-inside: avoid; margin-top: 15px;">
            <h4 style="text-align: left; text-decoration: underline; margin-bottom: 5px;">Anexos</h4>
            <table width="100%" style="border: none;">
                <tr>
                    @if ($imagenDiesel)
                        <td style="text-align: center; border: none;">
                            <p style="margin: 0;"><strong>Comprobante DIESEL</strong></p>
                            <img src="{{ $imagenDiesel }}" alt="Imagen DIESEL"
                                style="max-width: 220px; max-height: 200px; margin: 5px;">
                        </td>
                    @endif
                    @if ($imagenOtros)
                        <td style="text-align: center; border: none;">
                            <p style="margin: 0;"><strong>Comprobante OTROS</strong></p>
                            <img src="{{ $imagenOtros }}" alt="Imagen OTROS"
                                style="max-width: 220px; max-height: 200px; margin: 5px;">
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    @endif



    <!-- FIRMA -->
    {{-- <div class="signature">
        <p>______________________________</p>
        <p>Firma del responsable</p>
        <p><strong>{{ $user->NOMBRE }} {{ $user->APELLIDO }}</strong></p>
    </div> --}}


    {{-- <table width="100%" class="prod-table">
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
    </table> --}}

</body>

</html>
