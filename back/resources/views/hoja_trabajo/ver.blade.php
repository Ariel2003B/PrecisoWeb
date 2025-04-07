@extends('layout')

@section('Titulo', 'Ver Hoja de Trabajo')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Hoja de Trabajo No. {{ $hoja->numero_hoja ?? 'S/N' }}</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li><a href="{{ route('empresa.index') }}">Empresas</a></li>
                        <li class="current">Hoja de Trabajo</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">
                <h2>Detalles de la Hoja de Trabajo</h2>

                <table class="table table-bordered mb-4">
                    <tr>
                        <th>Fecha</th>
                        <td>{{ $hoja->fecha }}</td>
                    </tr>
                    <tr>
                        <th>Tipo de Día</th>
                        <td>{{ $hoja->tipo_dia }}</td>
                    </tr>
                    <tr>
                        <th>Ruta</th>
                        <td>{{ $hoja->ruta->descripcion }}</td>
                    </tr>
                    <tr>
                        <th>Unidad</th>
                        <td>{{ $hoja->unidad->placa }} ({{ $hoja->unidad->numero_habilitacion }})</td>
                    </tr>
                </table>

                <h3>Producción del Conductor</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hoja->producciones as $prod)
                            <tr>
                                <td>{{ $prod->nro_vuelta }}</td>
                                <td>{{ $prod->hora_subida }}</td>
                                <td>{{ $prod->hora_bajada }}</td>
                                <td>{{ number_format($prod->valor_vuelta, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p><strong>Total Producción: ${{ number_format($totalProduccion, 2) }}</strong></p>

                <h3>Reporte Fiscalizador</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
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
                    </tbody>
                </table>
                <p><strong>Total Fiscalizador: ${{ number_format($totalUsuario, 2) }}</strong></p>

                <h3>Gastos</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tipo de Gasto</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gastos as $tipo => $valor)
                            <tr>
                                <td>{{ $tipo }}</td>
                                <td>{{ number_format($valor, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><strong>Total de Gastos</strong></td>
                            <td><strong>{{ number_format($totalGastos, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>

                <h3>Cálculos Finales</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>Total Recaudo </th>
                        <td>{{ number_format(max($totalProduccion, $totalUsuario), 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total a Depositar</th>
                        <td>{{ number_format($totalADepositar, 2) }}</td>
                    </tr>
                </table>

                <a href="{{ route('reportes.index') }}" class="btn btn-secondary">Regresar</a>
                <a href="{{ url('/api/hojas-trabajo/' . $hoja->id_hoja . '/generar-pdfWeb') }}" class="btn btn-danger"
                    target="_blank">Descargar PDF</a>

            </div>
        </section>
    </main>
@endsection
