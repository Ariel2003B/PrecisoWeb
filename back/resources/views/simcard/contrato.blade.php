@extends('layout')

@section('Titulo', 'PrecisoGPS - Contrato SIM')
@section('ActivarBlog', '') {{-- desactiva menú blog si aplica --}}
@section('content')
    @php
        use Illuminate\Support\Str;
    @endphp
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.css">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

    <main class="main compact-ui">

        <!-- Page Title -->
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Contrato SIM #{{ $simcard->ID_SIM }}</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Contrato SIM</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7">
                        @if (!$puedeEditar)
                            <div class="alert alert-info">
                                <i class="bi bi-plus-circle me-1"></i>
                                No existe contrato vigente. Puedes crear uno nuevo ahora.
                            </div>
                        @endif
                        @if (session('ok'))
                            <div class="alert alert-success">{{ session('ok') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Datos rápidos de la SIM --}}
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row gy-2">
                                    <div class="col-md-3"><strong>Número:</strong> {{ $simcard->NUMEROTELEFONO }}</div>
                                    <div class="col-md-3"><strong>Plan:</strong> {{ $simcard->PLAN }}</div>
                                    <div class="col-md-3"><strong>Propietario:</strong> {{ $simcard->PROPIETARIO }}</div>
                                    <div class="col-md-3"><strong>Estado:</strong> {{ $simcard->ESTADO }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Solo mostrar el formulario si se puede crear/editar --}}
                        @php
                            $puedeVenderHardware = false;

                            if (!empty($detalle) && $detalle->cuotas && $detalle->cuotas->count() > 0) {
                                // saldo == 0 y TODAS las cuotas con comprobante
                                $saldoCero = (float) $detalle->SALDO <= 0;

                                $todasConComprobante = $detalle->cuotas->every(function ($c) {
                                    return !empty($c->COMPROBANTE);
                                });

                                $puedeVenderHardware = $saldoCero && $todasConComprobante;
                            }
                        @endphp

                        <form method="POST" action="{{ route('simcards.contrato.store', $simcard->ID_SIM) }}"
                            id="form-contrato" enctype="multipart/form-data">
                            @csrf

                            {{-- Si existe vigente, es edición --}}
                            @if (!empty($detalle))
                                <input type="hidden" name="DET_ID" value="{{ $detalle->DET_ID }}">
                            @endif

                            {{-- Propietario --}}
                            <div class="card mb-4">
                                <div class="card-header fw-bold">Propietario</div>
                                <div class="card-body">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-8">
                                            <label class="form-label">Cliente *</label>
                                            <div class="input-group flex-nowrap">
                                                <select name="USU_ID" id="USU_ID" class="form-select js-user-select"
                                                    required>
                                                    <option value="">-- Selecciona --</option>
                                                    @foreach ($usuarios as $u)
                                                        <option value="{{ $u->USU_ID }}" @selected(old('USU_ID', $simcard->USU_ID) == $u->USU_ID)>
                                                            {{ $u->APELLIDO }} {{ $u->NOMBRE }}
                                                        </option>
                                                    @endforeach
                                                </select>


                                                <button type="button" id="btn-clear-usu" class="btn btn-outline-secondary">
                                                    Quitar
                                                </button>
                                            </div>
                                            <br>
                                            <a href="{{ route('usuario.index') }}" class="btn btn-primary text-nowrap"
                                                target="_blank" title="Administrar clientes">
                                                Administrar Clientes
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Detalle de Contrato (prefill si hay $detalle) --}}
                            <div class="card mb-4">
                                <div class="card-header fw-bold">Hardware</div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Fecha de instalacion *</label>
                                            <input type="date" name="FECHA_ACTIVACION_RENOVACION"
                                                id="FECHA_ACTIVACION_RENOVACION" class="form-control" required
                                                value="{{ old('FECHA_ACTIVACION_RENOVACION', optional(optional($detalle)->FECHA_ACTIVACION_RENOVACION)->format('Y-m-d')) }}">

                                        </div>





                                        <div class="col-md-4">
                                            <label class="form-label">Valor total *</label>
                                            <input type="number" step="0.01" min="0" name="VALOR_TOTAL"
                                                id="VALOR_TOTAL" class="form-control" required
                                                value="{{ old('VALOR_TOTAL', optional($detalle)->VALOR_TOTAL) }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Número de cuotas *</label>
                                            <input type="number" min="1" max="60" name="NUMERO_CUOTAS"
                                                id="NUMERO_CUOTAS" class="form-control" required
                                                value="{{ old('NUMERO_CUOTAS', optional($detalle)->NUMERO_CUOTAS ?? 1) }}">
                                        </div>

                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="button" id="btn-distribuir"
                                                class="btn btn-outline-primary w-100">
                                                Distribuir valor y fechas en cuotas
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Cuotas (una sola lista, editable y ajustable por JS) --}}
                            <div class="card mb-4">
                                <div class="card-header fw-bold">Cuotas</div>
                                <div class="card-body">

                                    {{-- DET_ID oculto si existe, para que SIEMPRE viaje al servidor --}}
                                    @if (!empty($detalle))
                                        <input type="hidden" name="DET_ID" value="{{ $detalle->DET_ID }}">
                                    @endif

                                    <div id="cuotas-list">
                                        @if (!empty($detalle) && $detalle->cuotas->count())
                                            @foreach ($detalle->cuotas->sortBy('FECHA_PAGO')->values() as $i => $c)
                                                <div class="border rounded p-3 mb-3 cuota-item"
                                                    data-has-file="{{ $c->COMPROBANTE ? 1 : 0 }}">
                                                    <input type="hidden" name="cuotas[{{ $i }}][CUO_ID]"
                                                        value="{{ $c->CUO_ID }}">
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-3">
                                                            <label class="form-label">#{{ $i + 1 }} Fecha
                                                                pago programada</label>
                                                            <input type="date"
                                                                name="cuotas[{{ $i }}][FECHA_PAGO]"
                                                                class="form-control cuotas-fecha"
                                                                value="{{ old("cuotas.$i.FECHA_PAGO", optional($c->FECHA_PAGO)->format('Y-m-d')) }}">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label class="form-label">Valor cuota</label>
                                                            <input type="number" step="0.01" min="0"
                                                                name="cuotas[{{ $i }}][VALOR_CUOTA]"
                                                                class="form-control cuotas-valor"
                                                                value="{{ old("cuotas.$i.VALOR_CUOTA", $c->VALOR_CUOTA) }}">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="form-label d-block">Pagado</label>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input cuota-paid" type="checkbox"
                                                                    name="cuotas[{{ $i }}][PAGADO]"
                                                                    @checked(old("cuotas.$i.PAGADO", $c->COMPROBANTE ? true : false))>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Fecha de pago</label>
                                                            <input type="date" readonly
                                                                name="cuotas[{{ $i }}][FECHA_REAL_PAGO]"
                                                                class="form-control cuota-fecha-real"
                                                                value="{{ old("cuotas.$i.FECHA_REAL_PAGO", optional($c->FECHA_REAL_PAGO)->format('Y-m-d')) }}">
                                                        </div>

                                                        {{-- Archivo / Comprobante --}}
                                                        @if ($c->COMPROBANTE)
                                                            {{-- hay comprobante: NO mostrar input file por defecto --}}
                                                            <div class="col-md-3">
                                                                <label class="form-label d-block">Comprobante</label>
                                                                @php $isFile = \Illuminate\Support\Str::startsWith($c->COMPROBANTE, ['simcards/']); @endphp
                                                                @if ($isFile)
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-secondary btn-ver-comprobante"
                                                                        data-url="{{ asset('back/storage/app/public/' . $c->COMPROBANTE) }}">
                                                                        Ver
                                                                    </button>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-secondary btn-ver-comprobante"
                                                                        data-url="{{ $c->COMPROBANTE }}">
                                                                        Abrir
                                                                    </button>
                                                                @endif

                                                                {{-- input file oculto por defecto --}}
                                                                <input type="file"
                                                                    name="cuotas[{{ $i }}][COMPROBANTE_FILE]"
                                                                    class="form-control cuota-file d-none mt-2"
                                                                    accept=".jpg,.jpeg,.png,.pdf">
                                                            </div>
                                                        @else
                                                            {{-- sin comprobante: mostrar input file normalmente --}}
                                                            <div class="col-md-9">
                                                                <label class="form-label">Comprobante (archivo)</label>
                                                                <input type="file"
                                                                    name="cuotas[{{ $i }}][COMPROBANTE_FILE]"
                                                                    class="form-control cuota-file"
                                                                    accept=".jpg,.jpeg,.png,.pdf">
                                                            </div>
                                                        @endif

                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <div class="form-text">Puedes subir imagen (jpg/png) o PDF como comprobante.</div>
                                </div>
                            </div>

                            <div class="card mb-4" id="servicio-card"
                                data-serv-has-file="{{ !empty(optional($servicioReciente)->COMPROBANTE) ? 1 : 0 }}">
                                <div class="card-header fw-bold">Servicio</div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            {{-- si existe un servicio reciente, enviar su ID para actualizarlo --}}
                                            <input type="hidden" name="SERV_ID"
                                                value="{{ optional($servicioReciente)->SERV_ID }}">

                                            <label class="form-label">Fecha de activación *</label>
                                            <input type="date" name="SERV_FECHA" id="SERV_FECHA" class="form-control"
                                                value="{{ old('SERV_FECHA', optional(optional($servicioReciente)->FECHA_SERVICIO)->format('Y-m-d')) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Valor *</label>
                                            <input type="number" step="0.01" min="0" name="SERV_VALOR"
                                                id="SERV_VALOR" class="form-control"
                                                value="{{ old('SERV_VALOR', optional($servicioReciente)->VALOR_PAGO) }}">
                                        </div>
                                        {{-- Switch Pagado --}}
                                        <div class="col-md-2">
                                            <label class="form-label d-block">Pagado</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="SERV_PAGADO"
                                                    name="SERV_PAGADO" @checked(old('SERV_PAGADO', !empty(optional($servicioReciente)->COMPROBANTE)))>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Vigencia (meses) *</label>
                                            <input type="number" min="1" max="60" name="SERV_PLAZO"
                                                id="SERV_PLAZO" class="form-control"
                                                value="{{ old('SERV_PLAZO', optional($servicioReciente)->PLAZO_CONTRATADO ?? 1) }}">
                                        </div>



                                        {{-- Comprobante --}}
                                        @php $servHasFile = !empty(optional($servicioReciente)->COMPROBANTE); @endphp

                                        @if ($servHasFile)
                                            <div class="col-md-6">
                                                <label class="form-label d-block">Comprobante</label>
                                                @php
                                                    $isFile = \Illuminate\Support\Str::startsWith(
                                                        optional($servicioReciente)->COMPROBANTE,
                                                        ['simcards/'],
                                                    );
                                                @endphp
                                                @if ($isFile)
                                                    <button type="button"
                                                        class="btn btn-sm btn-secondary btn-ver-comprobante"
                                                        data-url="{{ asset('back/storage/app/public/' . $servicioReciente->COMPROBANTE) }}">
                                                        Ver
                                                    </button>
                                                @else
                                                    <button type="button"
                                                        class="btn btn-sm btn-secondary btn-ver-comprobante"
                                                        data-url="{{ $servicioReciente->COMPROBANTE }}">
                                                        Abrir
                                                    </button>
                                                @endif



                                                <input type="file" name="SERV_COMPROBANTE_FILE"
                                                    id="SERV_COMPROBANTE_FILE" class="form-control d-none mt-2"
                                                    accept=".jpg,.jpeg,.png,.pdf">
                                            </div>
                                        @else
                                            <div class="col-md-6">
                                                <label class="form-label">Comprobante (archivo)</label>
                                                <input type="file" name="SERV_COMPROBANTE_FILE"
                                                    id="SERV_COMPROBANTE_FILE" class="form-control"
                                                    accept=".jpg,.jpeg,.png,.pdf">
                                            </div>
                                        @endif

                                        <div class="col-md-4">
                                            <label class="form-label">Siguiente pago (auto)</label>
                                            <input type="date" name="SERV_SIGUIENTE_PAGO" id="SERV_SIGUIENTE_PAGO"
                                                class="form-control" readonly
                                                value="{{ old('SERV_SIGUIENTE_PAGO', optional(optional($servicioReciente)->FECHA_SIGUIENTE_PAGO)->format('Y-m-d')) }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Observación</label>
                                            <textarea name="SERV_OBSERVACION" class="form-control" rows="2">{{ old('SERV_OBSERVACION', optional($servicioReciente)->OBSERVACION) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <input type="hidden" name="modo" id="MODO" value="CONTRATO">
                            <div class="d-flex justify-content-between gap-2 flex-wrap">
                                <a href="{{ route('simcards.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Regresar Simcards
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Guardar
                                </button>

                                {{-- SOLO mostrar Renovar si el servicio actual tiene comprobante --}}
                                @if (!empty(optional($servicioReciente)->COMPROBANTE))
                                    <button type="submit" name="accion" value="renovar" class="btn btn-success">
                                        <i class="bi bi-arrow-repeat me-1"></i> Renovar servicio
                                    </button>
                                @endif

                                {{-- SOLO mostrar "Vender nuevo hardware" si el contrato actual está 100% pagado --}}
                                @if ($puedeVenderHardware)
                                    <button type="submit" name="accion" value="nuevo_hardware"
                                        class="btn btn-warning">
                                        <i class="bi bi-cpu me-1"></i> Vender hardware
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                    {{-- ========== DERECHA: HISTORIAL ========== --}}
                    <div class="col-lg-5">
                        <div class="card mb-4">
                            <div class="card-header fw-bold">Historial de instalacion</div>
                            <div class="card-body p-2">

                                @forelse ($historial as $h)
                                    @php
                                        $hoy = \Carbon\Carbon::today();

                                        // ACTIVACIÓN como Carbon|null
                                        $act = $h->FECHA_ACTIVACION_RENOVACION
                                            ? \Carbon\Carbon::parse($h->FECHA_ACTIVACION_RENOVACION)->startOfDay()
                                            : null;

                                        // VENCIMIENTO calculado:
                                        // 1) última cuota; 2) si no hay cuotas, activación + (num_cuotas-1) meses; 3) null si no hay datos
                                        $ultimaCuotaRaw = $h->cuotas?->max('FECHA_PAGO');
                                        if ($ultimaCuotaRaw) {
                                            $vencCalc = \Carbon\Carbon::parse($ultimaCuotaRaw)->endOfDay();
                                        } elseif ($act) {
                                            $n = max(1, (int) ($h->NUMERO_CUOTAS ?? 1));
                                            $vencCalc = $act
                                                ->copy()
                                                ->addMonths($n - 1)
                                                ->endOfDay();
                                        } else {
                                            $vencCalc = null;
                                        }

                                        // ESTADO (sin usar FECHA_SIGUIENTE_PAGO)
                                        if ($act && $hoy->lt($act)) {
                                            $estadoTxt = 'PROGRAMADO';
                                            $badgeClass = 'bg-info';
                                        } elseif ($vencCalc && $hoy->gt($vencCalc)) {
                                            $estadoTxt = 'FINALIZADO';
                                            $badgeClass = 'bg-secondary';
                                        } else {
                                            $estadoTxt = 'VIGENTE';
                                            $badgeClass = 'bg-success';
                                        }
                                    @endphp


                                    <div class="border rounded p-3 mb-3">
                                        <div
                                            class="d-flex justify-content-between align-items-start hist-head flex-wrap gap-2">
                                            <div class="hist-title">
                                                <span class="me-2">Activación:
                                                    {{ $act ? $act->toDateString() : '—' }}</span>


                                            </div>
                                            <span
                                                class="badge {{ $badgeClass }} flex-shrink-0">{{ $estadoTxt }}</span>
                                        </div>

                                        <div class="small text-muted mt-1">
                                            Plazo: <strong>{{ $h->PLAZO_CONTRATADO }}</strong> meses ·
                                            Total: <strong>${{ number_format($h->VALOR_TOTAL, 2) }}</strong> ·
                                            Cuotas: <strong>{{ $h->NUMERO_CUOTAS }}</strong>
                                        </div>


                                        {{-- Cuotas de este detalle --}}
                                        @if ($h->cuotas?->count())
                                            <div class="table-responsive mt-2">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 36%">Fecha</th>
                                                            <th style="width: 24%">Valor</th>
                                                            <th>Comp.</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($h->cuotas as $c)
                                                            @php $esArchivo = Str::startsWith($c->COMPROBANTE, ['simcards/']); @endphp
                                                            <tr>
                                                                <td>{{ optional($c->FECHA_PAGO)->format('Y-m-d') }}</td>
                                                                <td>${{ number_format($c->VALOR_CUOTA, 2) }}</td>
                                                                <td>
                                                                    @if ($c->COMPROBANTE)
                                                                        @if ($esArchivo)
                                                                            <button type="button"
                                                                                class="btn btn-xs btn-outline-secondary btn-ver-comprobante"
                                                                                data-url="{{ asset('back/storage/app/public/' . $c->COMPROBANTE) }}">
                                                                                Ver
                                                                            </button>
                                                                        @else
                                                                            <button type="button"
                                                                                class="btn btn-xs btn-outline-secondary btn-ver-comprobante"
                                                                                data-url="{{ $c->COMPROBANTE }}">
                                                                                Abrir
                                                                            </button>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted">—</span>
                                                                    @endif

                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="small text-muted mt-2">Sin cuotas registradas.</div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-muted">No hay historial todavía.</div>
                                @endforelse

                            </div>
                        </div>
                    </div>
                </div>
                {{-- ============ /GRID DOS COLUMNAS ============ --}}
            </div>
        </section>
    </main>
    <!-- Modal genérico para ver comprobantes -->
    <div class="modal fade" id="comprobanteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Comprobante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2 text-center">
                    {{-- Para imágenes --}}
                    <img id="comprobanteImg" src="" class="img-fluid d-none"
                        style="max-height: 70vh; object-fit: contain;" />

                    {{-- Para PDF u otros --}}
                    <iframe id="comprobanteFrame" src="" class="d-none"
                        style="width: 100%; height: 70vh; border: 0;" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('comprobanteModal');
            const frame = document.getElementById('comprobanteFrame');
            const img = document.getElementById('comprobanteImg');

            const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

            document.querySelectorAll('.btn-ver-comprobante').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = btn.getAttribute('data-url');
                    if (!url || !modal || (!frame && !img)) return;

                    // detectar si es imagen
                    const isImage = url.match(/\.(jpe?g|png|gif|webp)(\?|$)/i);

                    if (isImage) {
                        // mostrar IMG y ocultar iframe
                        if (frame) {
                            frame.classList.add('d-none');
                            frame.src = '';
                        }
                        if (img) {
                            img.src = url;
                            img.classList.remove('d-none');
                        }
                    } else {
                        // mostrar iframe (PDF u otros)
                        if (img) {
                            img.classList.add('d-none');
                            img.src = '';
                        }
                        if (frame) {
                            frame.src = url;
                            frame.classList.remove('d-none');
                        }
                    }

                    modal.show();
                });
            });

            // limpiar al cerrar
            modalEl?.addEventListener('hidden.bs.modal', () => {
                if (frame) {
                    frame.src = '';
                    frame.classList.add('d-none');
                }
                if (img) {
                    img.src = '';
                    img.classList.add('d-none');
                }
            });
        });
    </script>


    <script>
        const $fa = document.getElementById('FECHA_ACTIVACION_RENOVACION');
        const $nc = document.getElementById('NUMERO_CUOTAS');
        const $vt = document.getElementById('VALOR_TOTAL');
        const $list = document.getElementById('cuotas-list');
        const $btnDist = document.getElementById('btn-distribuir');
        const $modo = document.getElementById('MODO');
        // ======== SERVICIO: cálculo automático siguiente pago =========
        const $servFecha = document.getElementById('SERV_FECHA');
        const $servPlazo = document.getElementById('SERV_PLAZO');
        const $servSig = document.getElementById('SERV_SIGUIENTE_PAGO');

        function addMonthsToISO(isoDate, months) {
            if (!isoDate) return '';
            const d = new Date(isoDate + 'T00:00:00');
            d.setMonth(d.getMonth() + parseInt(months || 0, 10));
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function recalcServicioSiguiente() {
            const fa = $servFecha.value;
            const pl = parseInt($servPlazo.value || '0', 10);
            $servSig.value = (fa && pl > 0) ? addMonthsToISO(fa, pl) : '';
        }

        $servFecha?.addEventListener('change', recalcServicioSiguiente);
        $servPlazo?.addEventListener('input', recalcServicioSiguiente);

        function addMonthsToISO(isoDate, months) {
            if (!isoDate) return '';
            const d = new Date(isoDate + 'T00:00:00');
            d.setMonth(d.getMonth() + parseInt(months || 0, 10));
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function createCuotaRow() {
            const wrapper = document.createElement('div');
            wrapper.className = 'border rounded p-3 mb-3 cuota-item';
            wrapper.setAttribute('data-has-file', '0'); // nueva fila no tiene archivo previo
            wrapper.innerHTML = `
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label cuota-label"></label>
          <input type="date" class="form-control cuotas-fecha">
        </div>

        <div class="col-md-3">
          <label class="form-label">Valor cuota</label>
          <input type="number" step="0.01" min="0" class="form-control cuotas-valor">
        </div>

        <div class="col-md-2">
          <label class="form-label d-block">Pagado</label>
          <div class="form-check form-switch">
            <input class="form-check-input cuota-paid" type="checkbox">
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Fecha real de pago</label>
          <input type="date" class="form-control cuota-fecha-real">
        </div>

        <div class="col-md-9">
          <label class="form-label">Comprobante (archivo)</label>
          <input type="file" class="form-control cuota-file" accept=".jpg,.jpeg,.png,.pdf">
        </div>
      </div>`;
            return wrapper;
        }

        function renumberRows() {
            const rows = [...$list.querySelectorAll('.cuota-item')];
            rows.forEach((row, i) => {
                row.querySelector('.cuota-label').textContent = `#${i+1} Fecha pago`;
                row.querySelector('input.cuotas-fecha').name = `cuotas[${i}][FECHA_PAGO]`;
                row.querySelector('input.cuotas-valor').name = `cuotas[${i}][VALOR_CUOTA]`;

                const file = row.querySelector('input.cuota-file');
                if (file) file.name = `cuotas[${i}][COMPROBANTE_FILE]`;

                const hidden = row.querySelector('input[type="hidden"][name*="[CUO_ID]"]');
                if (hidden) hidden.name = `cuotas[${i}][CUO_ID]`;

                const paid = row.querySelector('input.cuota-paid');
                if (paid) paid.name = `cuotas[${i}][PAGADO]`;

                const fechaReal = row.querySelector('input.cuota-fecha-real');
                if (fechaReal) fechaReal.name = `cuotas[${i}][FECHA_REAL_PAGO]`;
            });
        }

        function syncCuotasCount() {
            const desired = Math.max(1, parseInt($nc.value || '1', 10));
            let current = $list.querySelectorAll('.cuota-item').length;
            while (current < desired) {
                $list.appendChild(createCuotaRow());
                current++;
            }
            while (current > desired) {
                $list.querySelector('.cuota-item:last-of-type')?.remove();
                current--;
            }
            renumberRows();
        }

        // Distribuye montos y fechas base: fecha de activación (o la 1ª fecha ya ingresada)
        function distribuir() {
            const n = $list.querySelectorAll('.cuota-item').length;
            const total = parseFloat($vt.value || '0');

            const fechas = $list.querySelectorAll('.cuotas-fecha');
            const montos = $list.querySelectorAll('.cuotas-valor');

            const baseMonto = Math.floor((total / n) * 100) / 100;
            let resto = +(total - baseMonto * n).toFixed(2);

            let fBase = $fa.value || (fechas[0]?.value ?? '');
            for (let i = 0; i < n; i++) {
                let v = baseMonto;
                if (resto > 0) {
                    v += 0.01;
                    resto = +(resto - 0.01).toFixed(2);
                }
                if (fBase) fechas[i].value = addMonthsToISO(fBase, i);
                montos[i].value = v.toFixed(2);
            }
        }

        // eventos
        $nc.addEventListener('input', syncCuotasCount);
        $btnDist.addEventListener('click', distribuir);
        // Validación: si "Pagado" está ON, exigir archivo si no hay existente
        document.getElementById('form-contrato').addEventListener('submit', function(e) {
            let ok = true;
            const rows = [...$list.querySelectorAll('.cuota-item')];

            rows.forEach((row, idx) => {
                const paid = row.querySelector('.cuota-paid')?.checked;
                if (!paid) return;

                const hasExisting = row.getAttribute('data-has-file') === '1';
                const file = row.querySelector('.cuota-file')?.files?.length > 0;

                // Si marked paid y no hay archivo nuevo ni existente => bloquear
                if (!file && !hasExisting) {
                    ok = false;
                    alert(`La cuota #${idx+1} está marcada como pagada. Debe subir un comprobante.`);
                }

                // FECHA_REAL_PAGO obligatoria cuando pagado
                const fr = row.querySelector('.cuota-fecha-real');
                if (fr && !fr.value) {
                    // si no puso fecha, la autocompletamos a HOY
                    const d = new Date();
                    const iso =
                        `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
                    fr.value = iso;
                }
            });

            if (!ok) e.preventDefault();
        });

        // // botones de envío con modo
        // document.getElementById('btn-guardar-contrato')?.addEventListener('click', (e) => {
        //     $modo.value = 'CONTRATO';
        //     e.target.closest('form').submit();
        // });
        // document.getElementById('btn-guardar-servicio')?.addEventListener('click', (e) => {
        //     $modo.value = 'SERVICIO';
        //     e.target.closest('form').submit();
        // });

        // inicial
        if ($list.querySelectorAll('.cuota-item').length === 0) syncCuotasCount();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.__userTomSelect?.destroy) window.__userTomSelect.destroy();

            window.__userTomSelect = new TomSelect('#USU_ID', {
                allowEmptyOption: true,
                maxOptions: 5000,
                placeholder: 'Buscar usuario…',
                shouldLoad: () => true,
                searchField: ['text'],
                plugins: ['dropdown_input'], // sin clear_button (usamos el externo)
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });

            // Botón externo para limpiar
            document.getElementById('btn-clear-usu')?.addEventListener('click', () => {
                window.__userTomSelect?.clear(); // limpia selección
                window.__userTomSelect?.setTextboxValue(''); // borra el texto del buscador
                document.getElementById('USU_ID')
                    ?.dispatchEvent(new Event('change')); // por si quieres reaccionar en JS
                window.__userTomSelect?.blur();
            });
        });
    </script>

    <script>
        // === Servicio: toggle de file y validación de pagado ===
        const $servCard = document.getElementById('servicio-card');
        const $servPagado = document.getElementById('SERV_PAGADO');
        const $servFile = document.getElementById('SERV_COMPROBANTE_FILE');
        const $servToggleBtn = document.getElementById('SERV_BTN_TOGGLE_FILE');

        // Si hay botón "Subir nuevo", al hacer click mostramos el input file oculto
        $servToggleBtn?.addEventListener('click', () => {
            $servFile?.classList.remove('d-none');
            $servFile?.focus();
        });

        // Envío: si Pagado = ON, debe haber archivo nuevo o ya existente en DB
        document.getElementById('form-contrato').addEventListener('submit', function(e) {
            let ok = true;

            // Servicio
            const servHasExisting = ($servCard?.getAttribute('data-serv-has-file') === '1');
            const servPaid = $servPagado?.checked;
            const hasNewFile = ($servFile && $servFile.files && $servFile.files.length > 0);

            if (servPaid && !servHasExisting && !hasNewFile) {
                ok = false;
                alert('Marcaste Servicio como pagado. Debes subir un comprobante.');
            }

            if (!ok) e.preventDefault();
        });
    </script>


    <style>
        .ts-wrapper .ts-control {
            position: relative;
            padding-right: 2rem;
        }

        .ts-wrapper .ts-control .ts-clear-button {
            position: absolute;
            right: .75rem;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            opacity: .6;
        }

        .ts-wrapper .ts-control .ts-clear-button:hover {
            opacity: 1;
        }
    </style>
    <style>
        /* ====== Modo compacto (no afecta otras páginas) ====== */
        .compact-ui {
            font-size: 0.92rem;
        }

        /* ↓ base de todo */
        .compact-ui .page-title h1 {
            font-size: 1.15rem;
            margin: 0;
        }

        .compact-ui .breadcrumbs {
            font-size: .85rem;
        }

        /* Cards y separaciones */
        .compact-ui .card {
            border-radius: .4rem;
        }

        .compact-ui .card-header {
            padding: .5rem .75rem;
            font-size: .95rem;
        }

        .compact-ui .card-body {
            padding: .75rem;
        }

        .compact-ui .alert {
            padding: .5rem .75rem;
            font-size: .92rem;
            margin-bottom: .75rem;
        }

        .compact-ui .mb-4 {
            margin-bottom: .9rem !important;
        }

        /* Formularios (inputs/selects/labels) */
        .compact-ui .form-label {
            margin-bottom: .25rem;
            font-size: .9rem;
        }

        /* NO tocar los file inputs, solo textos, números, fechas, etc. */
        .compact-ui .form-control:not([type="file"]) {
            padding: .25rem .5rem;
            font-size: .92rem;
            line-height: 1.25;
            height: calc(1.25rem + .5rem + 2px);
            /* altura visual más baja */
        }

        /* Estilo específico para los file inputs (cuotas + servicio) */
        .compact-ui input[type="file"].form-control,
        .compact-ui .cuota-file,
        .compact-ui #SERV_COMPROBANTE_FILE {
            /* restaurar algo cercano al estilo Bootstrap normal */
            padding: 0.375rem 0.75rem;
            font-size: 0.92rem;
            line-height: 1.5;
            height: auto;
            width: 100%;
            /* ocupa todo el ancho de la col-md-3 */
            display: block;
            /* que se comporte como un control completo */
            box-sizing: border-box;
        }


        /* Botones */
        .compact-ui .btn {
            padding: .3rem .55rem;
            font-size: .9rem;
            border-radius: .35rem;
        }

        .compact-ui .btn.btn-sm,
        .compact-ui .btn-xs {
            padding: .2rem .45rem;
            font-size: .8rem;
        }

        /* Filas y columnas más apretadas */
        .compact-ui .row.g-3 {
            --bs-gutter-x: .75rem;
            --bs-gutter-y: .5rem;
        }

        .compact-ui .row.gy-2 {
            --bs-gutter-y: .35rem;
        }

        /* Tablas */
        .compact-ui table.table {
            font-size: .9rem;
            margin-bottom: 0;
        }

        .compact-ui .table> :not(caption)>*>* {
            padding: .35rem .5rem;
        }

        /* Badges */
        .compact-ui .badge {
            font-size: .72rem;
            padding: .28em .45em;
        }

        /* Lista de cuotas: tarjetas finitas */
        .compact-ui .cuota-item {
            padding: .65rem !important;
        }

        /* TomSelect (selector de Usuario) */
        .compact-ui .ts-wrapper.single .ts-control {
            min-height: 32px;
            padding: 2px 28px 2px 8px;
        }

        .compact-ui .ts-dropdown {
            font-size: .9rem;
        }

        .compact-ui .ts-wrapper .ts-control .ts-clear-button {
            right: .5rem;
        }

        /* Contenedor más estrecho (opcional: comenta si no lo quieres) */
        .compact-ui .container {
            max-width: 1100px;
        }

        /* ===== Historial compacto: título + badge sin quiebres raros ===== */
        .compact-ui .hist-head {
            row-gap: .25rem;
        }

        .compact-ui .hist-title {
            font-weight: 600;
            line-height: 1.15;
        }

        .compact-ui .hist-title .no-break {
            white-space: nowrap;
        }

        /* En pantallas angostas, el badge pasa abajo y el título ocupa todo el ancho */
        @media (max-width: 576px) {
            .compact-ui .hist-head {
                gap: .25rem .5rem;
            }

            .compact-ui .hist-title {
                width: 100%;
            }

            .compact-ui .hist-head .badge {
                order: 2;
            }
        }

        /* Tabla del historial: más ordenada */
        .compact-ui .card .table th:nth-child(1),
        .compact-ui .card .table td:nth-child(1) {
            width: 38%;
        }

        .compact-ui .card .table th:nth-child(2),
        .compact-ui .card .table td:nth-child(2) {
            width: 22%;
            text-align: right;
        }

        .compact-ui .card .table th:nth-child(3),
        .compact-ui .card .table td:nth-child(3) {
            width: 40%;
        }

        .compact-ui .card .table .btn {
            padding: .15rem .45rem;
            font-size: .78rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            function setupFilePreview(selector) {
                document.querySelectorAll(selector).forEach(input => {

                    input.addEventListener('change', function() {
                        const file = this.files && this.files[0];

                        // buscar / crear contenedor de preview
                        let preview = this.parentElement.querySelector('.file-preview');
                        if (!preview) {
                            preview = document.createElement('div');
                            preview.className = 'file-preview mt-2';
                            this.parentElement.appendChild(preview);
                        }

                        // si borró el archivo
                        if (!file) {
                            preview.innerHTML = '';
                            return;
                        }

                        const fileName = file.name || '';
                        const ext = fileName.split('.').pop().toLowerCase();
                        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);

                        // limpiar contenido previo
                        preview.innerHTML = '';

                        if (isImage) {
                            const img = document.createElement('img');
                            img.className = 'img-fluid rounded shadow-sm';
                            img.style.maxHeight = '180px';
                            img.style.objectFit = 'contain';
                            img.alt = 'Vista previa del comprobante';
                            img.src = URL.createObjectURL(file); // blob local

                            preview.appendChild(img);
                        } else if (ext === 'pdf') {
                            const info = document.createElement('div');
                            info.className = 'small text-muted';
                            info.innerHTML = `PDF seleccionado: <strong>${fileName}</strong>`;
                            preview.appendChild(info);
                        } else {
                            const info = document.createElement('div');
                            info.className = 'small text-muted';
                            info.innerHTML = `Archivo seleccionado: <strong>${fileName}</strong>`;
                            preview.appendChild(info);
                        }
                    });

                });
            }

            // Vista previa para todas las cuotas
            setupFilePreview('.cuota-file');

            // Vista previa para el comprobante del servicio
            setupFilePreview('#SERV_COMPROBANTE_FILE');
        });
    </script>
    <style>
        .file-preview img {
            max-width: 100%;
        }
    </style>

@endsection
