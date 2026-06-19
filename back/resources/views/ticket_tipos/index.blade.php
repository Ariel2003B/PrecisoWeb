@extends('layout')

@section('Titulo', 'Tickets - ' . $empresa->NOMBRE)

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Tipos de Ticket</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('empresa.index') }}">Empresas</a></li>
                        <li class="current">Tickets - {{ $empresa->NOMBRE }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Formulario para agregar nuevo tipo --}}
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        Agregar nuevo tipo de ticket
                    </div>
                    <div class="card-body">
                        <form action="{{ route('ticket-tipos.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="EMP_ID" value="{{ $empresa->EMP_ID }}">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Nombre del ticket <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" class="form-control"
                                           placeholder="Ej: Ticket A, Medio pasaje" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Valor ($) <span class="text-danger">*</span></label>
                                    <input type="number" name="valor" step="0.0001" min="0"
                                           class="form-control" placeholder="Ej: 0.35" required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-plus-circle"></i> Agregar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tabla de tipos existentes --}}
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Valor ($)</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td>
                                    <form action="{{ route('ticket-tipos.update', $ticket->id) }}" method="POST"
                                          class="d-flex gap-2 justify-content-center align-items-center" id="form-{{ $ticket->id }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="nombre" value="{{ $ticket->nombre }}"
                                               class="form-control form-control-sm" style="max-width: 180px;">
                                </td>
                                <td>
                                        <input type="number" name="valor" step="0.0001" min="0"
                                               value="{{ $ticket->valor }}"
                                               class="form-control form-control-sm" style="max-width: 120px;">
                                </td>
                                <td>
                                    @if ($ticket->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-pencil"></i> Guardar
                                        </button>
                                    </form>
                                    <form action="{{ route('ticket-tipos.toggle', $ticket->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $ticket->activo ? 'btn-warning' : 'btn-success' }}">
                                            {{ $ticket->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No hay tipos de ticket configurados para esta empresa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <a href="{{ route('empresa.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver a Empresas
                </a>
            </div>
        </section>
    </main>
@endsection
