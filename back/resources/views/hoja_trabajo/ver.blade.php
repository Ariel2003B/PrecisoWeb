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

<!-- Modal clave edición pasajeros -->
<div id="modalClavePasajeros" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:8px;padding:24px 28px;width:260px;box-shadow:0 4px 20px rgba(0,0,0,0.25);">
        <p style="margin:0 0 12px;font-weight:600;font-size:14px;color:#333;">Ingresa la clave para editar</p>
        <input id="inputClavePasajeros" type="password" maxlength="10"
               style="width:100%;padding:6px 10px;border:1px solid #ccc;border-radius:4px;font-size:15px;letter-spacing:4px;text-align:center;"
               placeholder="••••">
        <p id="claveError" style="color:#dc3545;font-size:12px;margin:6px 0 0;display:none;">Clave incorrecta</p>
        <div style="display:flex;gap:8px;margin-top:14px;">
            <button id="btnClaveOk" style="flex:1;padding:6px;background:#0d6efd;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">Confirmar</button>
            <button id="btnClaveCancelar" style="flex:1;padding:6px;background:#6c757d;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">Cancelar</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var CLAVE = '6810';
    var sesionAutorizada = false;
    var pendingSpan = null;

    var modal        = document.getElementById('modalClavePasajeros');
    var inputClave   = document.getElementById('inputClavePasajeros');
    var claveError   = document.getElementById('claveError');
    var btnOk        = document.getElementById('btnClaveOk');
    var btnCancelar  = document.getElementById('btnClaveCancelar');

    function abrirModal(span) {
        pendingSpan = span;
        inputClave.value = '';
        claveError.style.display = 'none';
        modal.style.display = 'flex';
        setTimeout(function() { inputClave.focus(); }, 50);
    }

    function cerrarModal() {
        modal.style.display = 'none';
        pendingSpan = null;
    }

    function verificarClave() {
        if (inputClave.value === CLAVE) {
            sesionAutorizada = true;
            cerrarModal();
            activarEdicion(pendingSpan);
        } else {
            claveError.style.display = 'block';
            inputClave.value = '';
            inputClave.focus();
        }
    }

    btnOk.addEventListener('click', verificarClave);
    btnCancelar.addEventListener('click', cerrarModal);
    inputClave.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') verificarClave();
        if (e.key === 'Escape') cerrarModal();
    });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) cerrarModal();
    });

    function activarEdicion(span) {
        if (span.classList.contains('editing')) return;
        span.classList.add('editing');

        var prodId   = span.dataset.prodId;
        var current  = span.textContent.trim();
        var original = current === '—' ? '' : current;

        var input = document.createElement('input');
        input.type  = 'number';
        input.min   = '0';
        input.value = original;
        input.style.cssText = 'width:70px;padding:1px 4px;font-size:inherit;border:1px solid #666;border-radius:3px;';

        span.textContent = '';
        span.appendChild(input);
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

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter')  guardar(span);
            if (e.key === 'Escape') { span.textContent = original || '—'; span.classList.remove('editing'); }
        });
        input.addEventListener('blur', function() {
            if (span.classList.contains('editing')) guardar(span);
        });
    }

    document.querySelectorAll('.pasajeros-val').forEach(function(span) {
        span.style.cursor = 'pointer';
        span.title = 'Clic para editar';

        span.addEventListener('mouseenter', function() {
            if (!this.classList.contains('editing'))
                this.style.borderBottom = '1px dashed #aaa';
        });
        span.addEventListener('mouseleave', function() {
            if (!this.classList.contains('editing'))
                this.style.borderBottom = '1px dashed transparent';
        });

        span.addEventListener('click', function() {
            if (this.classList.contains('editing')) return;
            if (sesionAutorizada) {
                activarEdicion(this);
            } else {
                abrirModal(this);
            }
        });
    });
})();
</script>
@endpush
@endsection
