@extends('layout')
@section('Titulo', 'Geocercas / Sanciones por minuto')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Geocercas – {{ $empresa->NOMBRE }}</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('empresa.index') }}">Empresas</a></li>
                        <li class="current">Geocercas</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif


                <form method="POST" action="{{ route('empresa.stops.save', $empresa->EMP_ID) }}">
                    @csrf

                    <div class="d-flex align-items-center gap-2 mb-2">
                        <label class="form-label mb-0">Aplicar a todos:</label>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                            id="fillAll" placeholder="0.50">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnFill">Aplicar</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle" style="font-size:.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:120px;">Nimbus ID</th>
                                    <th>Nombre geocerca</th>
                                    <th style="width:160px;" class="text-end">Valor por minuto (USD)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $r)
                                    <tr>
                                        <td class="text-muted">{{ $r['nimbus_id'] }}</td>
                                        <td>{{ $r['nombre'] }}</td>
                                        <td class="text-end">
                                            <input type="number" name="stops[{{ $r['nimbus_id'] }}][valor]"
                                                value="{{ number_format($r['valor'], 2, '.', '') }}" step="0.01"
                                                min="0" class="form-control form-control-sm text-end inp-valor">
                                            <input type="hidden" name="stops[{{ $r['nimbus_id'] }}][nombre]"
                                                value="{{ $r['nombre'] }}">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No se recibieron paradas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-success">Guardar</button>
                        <a href="{{ route('empresa.index') }}" class="btn btn-secondary">Volver</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btnFill');
            const val = document.getElementById('fillAll');
            btn?.addEventListener('click', () => {
                const v = parseFloat(val.value);
                if (isNaN(v) || v < 0) return;
                document.querySelectorAll('.inp-valor').forEach(inp => inp.value = v.toFixed(2));
            });
        });
    </script>
@endsection
