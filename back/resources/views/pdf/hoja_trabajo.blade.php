<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin-top: 100px;
            margin-bottom: 120px;
            margin-left: 50px;
            margin-right: 50px;
        }


        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .footer {
            position: fixed;
            bottom: -60px;
            /* Esto baja el footer al final real del PDF */
            left: 0;
            right: 0;
            height: 100px;
            font-size: 11px;
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
            <!-- Cambios en la cabecera para mostrar el número de hoja -->
            <td style="width: 50%; text-align: center; border: none;">
                <h2 style="margin: 0;">HOJA DE TRABAJO No. {{ $hoja->numero_hoja ?? 'S/N' }}</h2>
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
            <td width="45%" style="vertical-align: top;">
                <table width="100%">
                    <thead>
                        <tr>
                            <th colspan="4" class="section-title" style="text-align:left;">
                                REPORTE CONDUCTOR
                            </th>
                        </tr>
                        <tr>
                            <td colspan="4" class="bold" style="text-align:left; padding: 6px 0;">
                                Conductor: {{ $hoja->conductor->nombre }}
                            </td>
                        </tr>
                        <tr>
                            <th>No.</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Valor</th>
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
            <td width="45%" style="vertical-align: top;">
                @if ($vueltasUsuario && $vueltasUsuario->count())
                    <table width="100%">
                        <thead>
                            <tr>
                                <th colspan="4" class="section-title" style="text-align:left;">
                                    REPORTE FISCALIZADOR
                                </th>
                            </tr>
                            <tr>
                                <td colspan="4" class="bold" style="text-align:center; padding: 6px 0;">
                                    Fiscalizador: {{ $vueltasUsuario->first()->usuario->NOMBRE ?? '' }}
                                    {{ $vueltasUsuario->first()->usuario->APELLIDO ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <th>No.</th>
                                <th>Completos</th>
                                <th>Medios</th>
                                <th>Valor</th>
                                {{-- <th>Diferencia</th> --}}
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
                                <td colspan="3" class="left">TOTAL FISCALIZADOR</td>
                                <td>{{ number_format($vueltasUsuario->sum('valor_vuelta'), 2) }}</td>
                            </tr>
                        </tbody>

                    </table>
                @endif
            </td>
            <td width="10%" style="vertical-align: top;">
                <table>
                    <thead>
                        <tr>
                            <th colspan="1" class="section-title" style="text-align:left;">
                                DIFERENCIA
                            </th>
                        </tr>
                        <tr>
                            <td colspan="1" class="bold" style="text-align:center; padding: 6px 0;">
                                {{ '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vueltasUsuario as $vu)
                            @php
                                $produccionChofer = $hoja->producciones->firstWhere('nro_vuelta', $vu->nro_vuelta);
                                $valorChofer = $produccionChofer ? $produccionChofer->valor_vuelta : 0;
                                $diferencia = $valorChofer - $vu->valor_vuelta;

                                $diferenciaTexto =
                                    $diferencia > 0
                                        ? '+' . number_format($diferencia, 2)
                                        : ($diferencia < 0
                                            ? number_format($diferencia, 2)
                                            : '0');

                                $colorDiferencia =
                                    $diferencia > 0 ? 'rgb(49, 115, 16)' : ($diferencia < 0 ? 'red' : 'black');
                            @endphp
                            <tr>
                                <td><b style="color: {{ $colorDiferencia }}">{{ $diferenciaTexto }}</b></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
                            El conductor registró <span
                                style="color: rgb(49, 115, 16);">{{ number_format($diferencia, 2) }} dólares
                                más</span> que el conteo del fiscalizador.
                        @elseif ($diferencia < 0)
                            El conductor registró <span style="color: red;">${{ number_format(abs($diferencia), 2) }}
                                dólares menos</span> que el conteo del fiscalizador.
                        @else
                            El conductor y el fiscalizador registraron la misma cantidad de dinero.
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif

    @php
        $totalProduccion = $hoja->producciones->sum('valor_vuelta');
        $totalUsuario = $vueltasUsuario->sum('valor_vuelta');
        $totalGastos = 0;

        // Calcular total de gastos
        foreach (['DIESEL', 'CONDUCTOR', 'AYUDANTE', 'ALIMENTACION', 'OTROS'] as $tipo) {
            $totalGastos += $hoja->gastos->where('tipo_gasto', $tipo)->sum('valor');
        }

        // Seleccionar el mayor entre Total Producción y Total Usuario
        $totalMayor = max($totalProduccion, $totalUsuario);

        // Calcular Total a Depositar restando los gastos
        $totalADepositar = $totalMayor - $totalGastos;
    @endphp

    <!-- GASTOS Y TOTAL FINAL -->
    <table width="100%" class="gastos-table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th colspan="2">GASTOS</th>
            </tr>
        </thead>
        <tbody>
            @foreach (['DIESEL', 'CONDUCTOR', 'AYUDANTE', 'ALIMENTACION', 'OTROS'] as $tipo)
                @php
                    $valor = $hoja->gastos->where('tipo_gasto', $tipo)->sum('valor');
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
                <td class="total-row-depot">{{ number_format($totalADepositar, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($imagenDiesel || $imagenOtros)
        <div style="margin-top: 5px;">
            <h4 style="text-align: left; text-decoration: underline; margin-bottom: 5px;">Anexos</h4>
            <table width="100%" style="border: none;">
                <tr>
                    @if ($imagenDiesel)
                        <td style="text-align: center; border: none; width: 50%;">
                            <p style="margin: 0;"><strong>Comprobante DIESEL</strong></p>
                            <img src="{{ $imagenDiesel }}" alt="Imagen DIESEL"
                                style="max-width: 180px; max-height: 160px; margin: 5px;">
                        </td>
                    @endif
                    @if ($imagenOtros)
                        <td style="text-align: center; border: none; width: 50%;">
                            <p style="margin: 0;"><strong>Comprobante OTROS</strong></p>
                            <img src="{{ $imagenOtros }}" alt="Imagen OTROS"
                                style="max-width: 180px; max-height: 160px; margin: 5px;">
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    @endif

    <div class="footer">
        <hr style="margin-bottom: 10px; border: none; border-top: 1px solid #ccc;">
        <table width="100%">
            <tr>
                <td width="50%" style="text-align: left; vertical-align: top;">
                    <strong>PrecisoGPS</strong><br>
                    E16 N53-209 y de los Cholanes<br>
                    Quito, 170514<br>
                    <strong>Celular:</strong> +593 99 045 3275<br>
                    <strong>Correo:</strong> ventas@precisogps.com<br>
                    <a href="www.precisogps.com">www.precisogps.com</a>

                </td>
                <td width="50%" style="text-align: right; vertical-align: top;">
                    <strong>Síguenos en redes:</strong><br>
                    <table style="border: none; margin-top: 5px; margin-left: auto;">
                        <tr>
                            <td style="border: none; padding: 2px;">
                                <img src="https://cdn-icons-png.flaticon.com/16/733/733547.png" alt="Facebook">
                            </td>
                            <td style="border: none; padding: 2px;">@PrecisoGPS</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px;">
                                <img src="https://cdn-icons-png.flaticon.com/16/2111/2111463.png" alt="Instagram">
                            </td>
                            <td style="border: none; padding: 2px;">@precisogps</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px;">
                                <img src="https://cdn-icons-png.flaticon.com/16/1384/1384060.png" alt="YouTube">
                            </td>
                            <td style="border: none; padding: 2px;">@PrecisoGPS</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px;">
                                <img src="https://cdn-icons-png.flaticon.com/16/3046/3046121.png" alt="TikTok">
                            </td>
                            <td style="border: none; padding: 2px;">@precisogps</td>
                        </tr>
                    </table>
                </td>

            </tr>
        </table>
    </div>



</body>

</html>
