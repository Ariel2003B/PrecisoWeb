@extends('layout')
@section('Titulo', 'Hojas de Trabajo')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Hojas de Trabajo</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Hojas de Trabajo</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">
                <h4 class="mb-4">Filtrar hojas de trabajo</h4>

                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Ruta</label>
                        <input type="text" name="ruta" class="form-control" placeholder="Buscar ruta..." value="{{ request('ruta') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Unidad</label>
                        <input type="text" name="unidad" class="form-control" placeholder="Buscar placa..." value="{{ request('unidad') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </form>

                <table class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo Día</th>
                            <th>Ruta</th>
                            <th>Unidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hojas as $hoja)
                            <tr>
                                <td>{{ $hoja->fecha }}</td>
                                <td>{{ $hoja->tipo_dia }}</td>
                                <td>{{ $hoja->ruta->descripcion ?? '-' }}</td>
                                <td>{{ $hoja->unidad->placa ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('reportes.create', $hoja->id_hoja) }}" class="btn btn-primary btn-sm">
                                        Registrar Reporte
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No se encontraron hojas de trabajo con los filtros aplicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
@endsection
