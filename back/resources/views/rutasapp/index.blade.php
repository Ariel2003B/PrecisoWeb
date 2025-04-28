@extends('layout')

@section('Titulo', 'Listado de Rutas')

@section('content')
<main class="main">
    <div class="page-title accent-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Listado de Rutas</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                    <li class="current"><a href="{{ route('rutasapp.index') }}">Rutas</a></li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section py-5">
        <div class="container">
            <a href="{{ route('rutasapp.create') }}" class="btn btn-primary mb-4">Crear Ruta</a>

            <div class="input-group mb-3" style="max-width: 400px;">
                <input type="text" id="filtroRutas" class="form-control" placeholder="Filtrar rutas...">
                <button class="btn btn-outline-primary" type="button" data-bs-toggle="tooltip" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Empresa</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rutas as $ruta)
                        <tr>
                            <td>{{ $ruta->descripcion }}</td>
                            <td>{{ $ruta->empresa->NOMBRE ?? 'Sin Empresa' }}</td>
                            <td>
                                <a href="{{ route('rutasapp.edit', $ruta->id_ruta) }}" class="btn btn-sm btn-warning">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filtroRutas').addEventListener('input', function() {
            const filtro = this.value.toLowerCase();
            const filas = document.querySelectorAll('table tbody tr');

            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                fila.style.display = textoFila.includes(filtro) ? '' : 'none';
            });
        });
    });
</script>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
