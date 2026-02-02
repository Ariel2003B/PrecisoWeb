@extends('layout')

@section('Titulo', 'Ventas')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Ventas</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Ventas</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">

                <div class="card shadow-sm p-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
                        <h2 class="fw-bold mb-0">Listado de Ventas</h2>

                        {{-- Luego aquí ponemos botón Crear --}}
                        <a href="{{ route('ventas.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Nueva Venta
                        </a>

                    </div>

                    {{-- Filtros --}}
                    <form method="GET" action="{{ route('ventas.index') }}" class="row g-2 align-items-end mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Buscar</label>
                            <input type="text" name="q" value="{{ $q }}" class="form-control"
                                placeholder="N° venta, cliente, empresa, RUC, correo...">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($estados as $st)
                                    <option value="{{ $st }}" @selected($estado === $st)>{{ $st }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <a class="btn btn-secondary w-100" href="{{ route('ventas.index') }}">
                                Limpiar
                            </a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th># Venta</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Empresa</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado</th>
                                    {{-- Acciones después --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventas as $v)
                                    <tr>
                                        <td class="fw-bold">{{ $v->NUMERO_VENTA }}</td>
                                        <td>{{ optional($v->FECHA)->format('Y-m-d H:i') }}</td>

                                        <td>
                                            {{ $v->cliente?->NOMBRE }} {{ $v->cliente?->APELLIDO }}
                                            <div class="text-muted" style="font-size: 12px;">
                                                {{ $v->cliente?->CORREO }}
                                            </div>
                                        </td>

                                        <td>
                                            {{ $v->vendedor?->NOMBRE }} {{ $v->vendedor?->APELLIDO }}
                                            <div class="text-muted" style="font-size: 12px;">
                                                {{ $v->vendedor?->CORREO }}
                                            </div>
                                        </td>

                                        <td>
                                            @if ($v->empresa)
                                                <div class="fw-semibold">{{ $v->empresa->NOMBRE }}</div>
                                                <div class="text-muted" style="font-size: 12px;">
                                                    RUC: {{ $v->empresa->RUC }}
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <td class="text-end fw-bold">
                                            $ {{ number_format((float) $v->TOTAL, 2, '.', ',') }}
                                        </td>

                                        <td class="text-center">
                                            @php
                                                $badge = match ($v->ESTADO) {
                                                    'PAGADO' => 'success',
                                                    'PARCIAL' => 'warning',
                                                    'ANULADO' => 'danger',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ $v->ESTADO }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No hay ventas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    <div class="mt-3">
                        {{ $ventas->links() }}
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
