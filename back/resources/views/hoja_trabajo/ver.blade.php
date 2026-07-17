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
                        <td>{{ $hoja->fecha ?? 'Sin fecha disponible' }}</td>
                    </tr>
                    <tr>
                        <th>Tipo de Día</th>
                        <td>{{ $hoja->tipo_dia ?? 'Sin tipo de día' }}</td>
                    </tr>
                    <tr>
                        <th>Ruta</th>
                        <td>{{ $hoja->ruta->descripcion ?? 'Sin descripción de ruta' }}</td>
                    </tr>
                    <tr>
                        <th>Unidad</th>
                        <td>{{ $hoja->unidad->placa ?? 'Sin placa' }}
                            ({{ $hoja->unidad->numero_habilitacion ?? 'Sin número de habilitación' }})</td>
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
                            <th>Cont. Pasajeros</th>
                            <th>Valor Pasajeros</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hoja->producciones as $prod)
                            <tr>
                                <td>{{ $prod->nro_vuelta ?? 'N/A' }}</td>
                                <td>{{ $prod->hora_subida ?? 'Sin hora' }}</td>
                                <td>{{ $prod->hora_bajada ?? 'Sin hora' }}</td>
                                <td>{{ $prod->valor_vuelta ? number_format($prod->valor_vuelta, 2) : '0.00' }}</td>
                                <td>
                                    <span class="pasajeros-val" data-prod-id="{{ $prod->id_produccion }}"
                                          style="cursor:pointer; border-bottom:1px dashed transparent;"
                                          title="Clic para editar">
                                        {{ $prod->pasajeros_subida ?? '—' }}
                                    </span>
                                </td>
                                <td class="valor-pasajeros-{{ $prod->id_produccion }}">
                                    @if (!is_null($prod->valor_pasajeros))
                                        ${{ number_format($prod->valor_pasajeros, 2) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No hay registros de producción.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Totales</strong></td>
                            <td><strong>${{ number_format($totalProduccion ?? 0, 2) }}</strong></td>
                            <td><strong>{{ $hoja->producciones->sum('pasajeros_subida') }}</strong></td>
                            <td><strong>${{ number_format($hoja->producciones->sum('valor_pasajeros'), 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                <p><strong>Total Producción: ${{ number_format($totalProduccion ?? 0, 2) }}</strong></p>

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
                        @forelse ($vueltasUsuario as $vu)
                            <tr>
                                <td>{{ $vu->nro_vuelta ?? 'N/A' }}</td>
                                <td>{{ $vu->pasaje_completo ?? 0 }}</td>
                                <td>{{ $vu->pasaje_medio ?? 0 }}</td>
                                <td>{{ $vu->valor_vuelta ? number_format($vu->valor_vuelta, 2) : '0.00' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No hay registros del fiscalizador.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <p><strong>Total Fiscalizador: ${{ number_format($totalUsuario ?? 0, 2) }}</strong></p>

                <h3>Gastos</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tipo de Gasto</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($gastos as $tipo => $valor)
                            <tr>
                                <td>{{ $tipo }}</td>
                                <td>{{ $valor ? number_format($valor, 2) : '0.00' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">No hay gastos registrados.</td>
                            </tr>
                        @endforelse
                        <tr>
                            <td><strong>Total de Gastos</strong></td>
                            <td><strong>{{ number_format($totalGastos ?? 0, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>

                <h3>Cálculos Finales</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>Total Recaudo</th>
                        <td>{{ number_format(max($totalProduccion ?? 0, $totalUsuario ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total a Depositar</th>
                        <td>{{ number_format($totalADepositar ?? 0, 2) }}</td>
                    </tr>
                </table>

                <a href="{{ route('reportes.index') }}" class="btn btn-secondary">Regresar</a>
                <a href="{{ url('/api/hojas-trabajo/' . ($hoja->id_hoja ?? 0) . '/generar-pdfWeb') }}"
                    class="btn btn-danger" target="_blank">Descargar PDF</a>
            </div>
        </section>
    </main>

@push('scripts')
<script>
document.querySelectorAll('.pasajeros-val').forEach(function(span) {
    span.addEventListener('mouseenter', function() {
        this.style.borderBottomColor = '#aaa';
    });
    span.addEventListener('mouseleave', function() {
        if (!this.classList.contains('editing')) {
            this.style.borderBottomColor = 'transparent';
        }
    });

    span.addEventListener('click', function() {
        if (this.classList.contains('editing')) return;
        this.classList.add('editing');

        var prodId   = this.dataset.prodId;
        var current  = this.textContent.trim();
        var original = current === '—' ? '' : current;

        var input = document.createElement('input');
        input.type  = 'number';
        input.min   = '0';
        input.value = original;
        input.style.cssText = 'width:70px;padding:1px 4px;font-size:inherit;border:1px solid #666;border-radius:3px;';

        this.textContent = '';
        this.appendChild(input);
        input.focus();
        input.select();

        var guardar = function(spanEl) {
            var val = parseInt(input.value, 10);
            if (isNaN(val) || val < 0) {
                spanEl.textContent = original || '—';
                spanEl.classList.remove('editing');
                return;
            }

            fetch('/produccion/' + prodId + '/pasajeros', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ pasajeros_subida: val })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                spanEl.textContent = data.pasajeros_subida;
                spanEl.classList.remove('editing');
                var valorCell = document.querySelector('.valor-pasajeros-' + prodId);
                if (valorCell) valorCell.textContent = '$' + data.valor_pasajeros;
            })
            .catch(function() {
                spanEl.textContent = original || '—';
                spanEl.classList.remove('editing');
            });
        };

        var self = this;
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter')  guardar(self);
            if (e.key === 'Escape') { self.textContent = original || '—'; self.classList.remove('editing'); }
        });
        input.addEventListener('blur', function() {
            if (self.classList.contains('editing')) guardar(self);
        });
    });
});
</script>
@endpush
@endsection
