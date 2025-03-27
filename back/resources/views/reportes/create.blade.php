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
                <h4 class="mb-4">Reporte para la hoja del {{ $hoja->fecha }} - Ruta: {{ $hoja->ruta->descripcion ?? '' }}</h4>

                <form method="POST" action="{{ route('reportes.store') }}">
                    @csrf
                    <input type="hidden" name="id_hoja" value="{{ $hoja->id_hoja }}">
                    <div id="vueltasContainer">
                        @php $contador = 0; @endphp
                        @foreach($registros as $registro)
                            <div class="vuelta mb-3">
                                <h5>Vuelta #{{ $registro->nro_vuelta }}</h5>
                                <input type="hidden" name="reportes[{{ $contador }}][nro_vuelta]" value="{{ $registro->nro_vuelta }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Pasajes Completos</label>
                                        <input type="number" name="reportes[{{ $contador }}][pasaje_completo]" class="form-control" value="{{ $registro->pasaje_completo }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Pasajes Medios</label>
                                        <input type="number" name="reportes[{{ $contador }}][pasaje_medio]" class="form-control" value="{{ $registro->pasaje_medio }}" required>
                                    </div>
                                </div>
                            </div>
                            @php $contador++; @endphp
                        @endforeach

                        @if($registros->isEmpty())
                            <div class="vuelta mb-3">
                                <h5>Vuelta #1</h5>
                                <input type="hidden" name="reportes[0][nro_vuelta]" value="1">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Pasajes Completos</label>
                                        <input type="number" name="reportes[0][pasaje_completo]" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Pasajes Medios</label>
                                        <input type="number" name="reportes[0][pasaje_medio]" class="form-control" required>
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
        let contador = {{ $contador }};

        function agregarVuelta() {
            contador++;
            let html = `
            <div class="vuelta mb-3">
                <h5>Vuelta #${contador}</h5>
                <input type="hidden" name="reportes[${contador - 1}][nro_vuelta]" value="${contador}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Pasajes Completos</label>
                        <input type="number" name="reportes[${contador - 1}][pasaje_completo]" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Pasajes Medios</label>
                        <input type="number" name="reportes[${contador - 1}][pasaje_medio]" class="form-control" required>
                    </div>
                </div>
            </div>`;
            document.getElementById('vueltasContainer').insertAdjacentHTML('beforeend', html);
        }
    </script>
@endsection
