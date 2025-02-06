@extends('layout')

@section('Titulo', 'Listado de Equipos y Accesorios')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Equipos y Accesorios</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current">Equipos y Accesorios</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Lista de Equipos y Accesorios</h2>
                    <a href="{{ route('equipos.create') }}" class="btn btn-primary">Añadir Nuevo</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Icono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($equipos as $equipo)
                                <tr>
                                    <td>{{ $equipo->EQU_ID }}</td>
                                    <td>{{ $equipo->EQU_NOMBRE }}</td>
                                    <td>${{ number_format($equipo->EQU_PRECIO, 2) }}</td>
                                    <td><i class="{{ $equipo->EQU_ICONO }} fs-1 text-primary"></i></td>
                                    <td>
                                        <a href="{{ route('equipos.edit', $equipo->EQU_ID) }}" class="btn btn-warning btn-sm">Editar</a>
                                        <form action="{{ route('equipos.destroy', $equipo->EQU_ID) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este equipo/accesorio?')">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
@endsection
