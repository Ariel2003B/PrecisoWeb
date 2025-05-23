@extends('layout')
@section('Titulo', 'Registrar Reporte')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Registrar Reporte</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('reportes.index') }}">Hojas de Trabajo</a></li>
                        <li class="current">Registrar Reporte</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">
                <h2>Unidad: {{$hoja->unidad->placa}} ({{$hoja->unidad->numero_habilitacion}})</h2>
                <h4 class="mb-4">Reporte para la hoja del {{ $hoja->fecha }} - Ruta: {{ $hoja->ruta->descripcion ?? '' }}
                </h4>


                <form method="POST" action="{{ route('reportes.store') }}">
                    @csrf
                    <input type="hidden" name="id_hoja" value="{{ $hoja->id_hoja }}">
                    <div id="vueltasContainer">
                        @foreach ($registros as $registro)
                            <div class="vuelta mb-3">
                                <h5>Vuelta #{{ $registro->nro_vuelta }}</h5>
                                <input type="hidden" name="reportes[{{ $registro->nro_vuelta }}][nro_vuelta]"
                                    value="{{ $registro->nro_vuelta }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Pasajes Completos</label>
                                        <input type="number" name="reportes[{{ $registro->nro_vuelta }}][pasaje_completo]"
                                            class="form-control"
                                            value="{{ $registro->pasaje_completo }}"
                                            @if ($permisoLectura) readonly @endif
                                            required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Pasajes Medios</label>
                                        <input type="number" name="reportes[{{ $registro->nro_vuelta }}][pasaje_medio]"
                                            class="form-control"
                                            value="{{ $registro->pasaje_medio }}"
                                            @if ($permisoLectura) readonly @endif
                                            required>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    
                        @if ($registros->isEmpty())
                            <!-- Si no hay registros, inicia desde la vuelta #1 -->
                            <div class="vuelta mb-3">
                                <h5>Vuelta #1</h5>
                                <input type="hidden" name="reportes[1][nro_vuelta]" value="1">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Pasajes Completos</label>
                                        <input type="number" name="reportes[1][pasaje_completo]" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Pasajes Medios</label>
                                        <input type="number" name="reportes[1][pasaje_medio]" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    


                    <button type="button" class="btn btn-secondary mt-3" onclick="agregarVuelta()">+ Añadir vuelta</button>
                    <button type="submit" class="btn btn-success mt-3">Guardar Reporte</button>
                </form>
            </div>
        </section>
    </main>

    <script>
        let contador = {{ $contador }}; // Contador inicial que viene desde el backend (¡Esto es importante!)
        if (contador === 1) {
            contador = 2;
        }

        function agregarVuelta() {
            let vueltaActual = contador; // Asigna el número actual de la vuelta
            contador++; // Incrementa el contador para la próxima vuelta

            let html = `
    <div class="vuelta mb-3">
        <h5>Vuelta #${vueltaActual}</h5>
        <input type="hidden" name="reportes[${vueltaActual}][nro_vuelta]" value="${vueltaActual}">
        <div class="row">
            <div class="col-md-4">
                <label>Pasajes Completos</label>
                <input type="number" name="reportes[${vueltaActual}][pasaje_completo]" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Pasajes Medios</label>
                <input type="number" name="reportes[${vueltaActual}][pasaje_medio]" class="form-control" required>
            </div>
        </div>
    </div>`;

            document.getElementById('vueltasContainer').insertAdjacentHTML('beforeend', html);
        }
    </script>
@endsection
