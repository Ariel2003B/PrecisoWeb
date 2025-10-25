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
                                            <div class="col-md-8">
                                                <label class="form-label">Usuario *</label>
                                                <div class="input-group">
                                                    <select name="USU_ID" id="USU_ID" class="form-select js-user-select"
                                                        required>
                                                        <option value="">-- Selecciona --</option>
                                                        @foreach ($usuarios as $u)
                                                            <option value="{{ $u->USU_ID }}"
                                                                @selected(old('USU_ID', $simcard->USU_ID) == $u->USU_ID)>
                                                                {{ $u->APELLIDO }} {{ $u->NOMBRE }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" id="btn-clear-usu"
                                                        class="btn btn-outline-secondary">
                                                        Quitar
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Detalle de Contrato (prefill si hay $detalle) --}}
                            <div class="card mb-4">
                                <div class="card-header fw-bold">Detalle de Contrato</div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Fecha de activación *</label>
                                            <input type="date" name="FECHA_ACTIVACION_RENOVACION"
                                                id="FECHA_ACTIVACION_RENOVACION" class="form-control" required
                                                value="{{ old('FECHA_ACTIVACION_RENOVACION', optional(optional($detalle)->FECHA_ACTIVACION_RENOVACION)->format('Y-m-d')) }}">

                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Plazo contratado (meses) *</label>
                                            <input type="number" min="1" max="60" name="PLAZO_CONTRATADO"
                                                id="PLAZO_CONTRATADO" class="form-control" required
                                                value="{{ old('PLAZO_CONTRATADO', optional($detalle)->PLAZO_CONTRATADO ?? 1) }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Siguiente pago (auto)</label>
                                            <input type="date" name="FECHA_SIGUIENTE_PAGO" id="FECHA_SIGUIENTE_PAGO"
                                                class="form-control" readonly
                                                value="{{ old('FECHA_SIGUIENTE_PAGO', optional(optional($detalle)->FECHA_SIGUIENTE_PAGO)->format('Y-m-d')) }}">

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
                                                <div class="border rounded p-3 mb-3 cuota-item">
                                                    {{-- Si es existente, incluir CUO_ID --}}
                                                    <input type="hidden" name="cuotas[{{ $i }}][CUO_ID]"
                                                        value="{{ $c->CUO_ID }}">
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-3">
                                                            <label class="form-label">#{{ $i + 1 }} Fecha
                                                                pago</label>
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
                                                        <div class="col-md-3">
                                                            <label class="form-label">Comprobante (archivo)</label>
                                                            <input type="file"
                                                                name="cuotas[{{ $i }}][COMPROBANTE_FILE]"
                                                                class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label d-block">Comprobante actual</label>
                                                            @php $isFile = \Illuminate\Support\Str::startsWith($c->COMPROBANTE, ['simcards/']); @endphp
                                                            @if ($c->COMPROBANTE)
                                                                @if ($isFile)
                                                                    <a href="{{ asset('back/storage/app/public/' . $c->COMPROBANTE) }}"
                                                                        target="_blank"
                                                                        class="btn btn-sm btn-secondary">Ver</a>
                                                                @else
                                                                    <a href="{{ $c->COMPROBANTE }}" target="_blank"
                                                                        class="btn btn-sm btn-secondary">Abrir</a>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <div class="form-text">Puedes subir imagen (jpg/png) o PDF como comprobante.</div>
                                </div>
                            </div>


                            <div class="d-flex justify-content-between">
                                <a href="{{ route('simcards.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Regresar Simcards
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Guardar contrato
                                </button>
                            </div>

                        </form>


                    </div>
                    {{-- ========== DERECHA: HISTORIAL ========== --}}
                    <div class="col-lg-5">
                        <div class="card mb-4">
                            <div class="card-header fw-bold">Historial de contratos</div>
                            <div class="card-body p-2">

                                @forelse ($historial as $h)
                                    @php
                                        $hoy = \Carbon\Carbon::today();
                                        $esVigente =
                                            $h->FECHA_ACTIVACION_RENOVACION && $h->FECHA_SIGUIENTE_PAGO
                                                ? $hoy->between(
                                                    \Carbon\Carbon::parse(
                                                        $h->FECHA_ACTIVACION_RENOVACION,
                                                    )->startOfDay(),
                                                    \Carbon\Carbon::parse($h->FECHA_SIGUIENTE_PAGO)->endOfDay(),
                                                )
                                                : false;

                                        $badgeClass = $esVigente
                                            ? 'bg-success'
                                            : (optional($h->FECHA_SIGUIENTE_PAGO)->lt($hoy)
                                                ? 'bg-secondary'
                                                : 'bg-info');

                                        $estadoTxt = $esVigente
                                            ? 'VIGENTE'
                                            : (optional($h->FECHA_SIGUIENTE_PAGO)->lt($hoy)
                                                ? 'FINALIZADO'
                                                : 'PROGRAMADO');
                                    @endphp

                                    <div class="border rounded p-3 mb-3">
                                        <div
                                            class="d-flex justify-content-between align-items-start hist-head flex-wrap gap-2">
                                            <div class="hist-title">
                                                <span class="me-2">Activación:
                                                    {{ optional($h->FECHA_ACTIVACION_RENOVACION)->format('Y-m-d') }}</span>
                                                <span class="no-break">
                                                    Vencimiento:
                                                    {{ optional($h->FECHA_SIGUIENTE_PAGO)->format('Y-m-d') }}
                                                </span>
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
                                                                            <a class="btn btn-xs btn-outline-secondary"
                                                                                target="_blank"
                                                                                href="{{ asset('back/storage/app/public/' . $c->COMPROBANTE) }}">Ver</a>
                                                                        @else
                                                                            <a class="btn btn-xs btn-outline-secondary"
                                                                                target="_blank"
                                                                                href="{{ $c->COMPROBANTE }}">Abrir</a>
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
    <script>
        const $fa = document.getElementById('FECHA_ACTIVACION_RENOVACION');
        const $pl = document.getElementById('PLAZO_CONTRATADO');
        const $fsp = document.getElementById('FECHA_SIGUIENTE_PAGO');
        const $nc = document.getElementById('NUMERO_CUOTAS');
        const $vt = document.getElementById('VALOR_TOTAL');
        const $list = document.getElementById('cuotas-list');
        const $btnDist = document.getElementById('btn-distribuir');

        function addMonthsToISO(isoDate, months) {
            if (!isoDate) return '';
            const d = new Date(isoDate + 'T00:00:00');
            d.setMonth(d.getMonth() + parseInt(months || 0, 10));
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function recalcSiguientePago() {
            const fa = $fa.value;
            const pl = parseInt($pl.value || '0', 10);
            $fsp.value = (fa && pl > 0) ? addMonthsToISO(fa, pl) : '';
        }

        // Crea una fila (nueva cuota sin CUO_ID)
        function createCuotaRow() {
            const wrapper = document.createElement('div');
            wrapper.className = 'border rounded p-3 mb-3 cuota-item';
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
        <div class="col-md-3">
          <label class="form-label">Comprobante (archivo)</label>
          <input type="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
        </div>
      </div>`;
            return wrapper;
        }

        // Renumera índices name="cuotas[i][campo]" y # visual
        function renumberRows() {
            const rows = [...$list.querySelectorAll('.cuota-item')];
            rows.forEach((row, i) => {
                const label = row.querySelector('.cuota-label');
                if (label) label.textContent = `#${i+1} Fecha pago`;

                // date
                const dateInput = row.querySelector('input.cuotas-fecha');
                dateInput.name = `cuotas[${i}][FECHA_PAGO]`;

                // valor
                const valInput = row.querySelector('input.cuotas-valor');
                valInput.name = `cuotas[${i}][VALOR_CUOTA]`;

                // file
                const fileInput = row.querySelector('input[type="file"]');
                fileInput.name = `cuotas[${i}][COMPROBANTE_FILE]`;

                // si existe un hidden CUO_ID (para filas existentes), su name debe corresponder al nuevo índice
                const hidden = row.querySelector('input[type="hidden"][name*="[CUO_ID]"]');
                if (hidden) hidden.name = `cuotas[${i}][CUO_ID]`;
            });
        }

        // Ajusta cantidad de filas al número requerido
        function syncCuotasCount() {
            const desired = Math.max(1, parseInt($nc.value || '1', 10));
            let current = $list.querySelectorAll('.cuota-item').length;

            // agregar
            while (current < desired) {
                $list.appendChild(createCuotaRow());
                current++;
            }
            // quitar (del final)
            while (current > desired) {
                const last = $list.querySelector('.cuota-item:last-of-type');
                if (last) last.remove();
                current--;
            }
            renumberRows();
        }

        function distribuir() {
            const n = $list.querySelectorAll('.cuota-item').length;
            const total = parseFloat($vt.value || '0');
            const fBase = $fsp.value;

            const valores = [];
            if (n > 0) {
                const base = Math.floor((total / n) * 100) / 100;
                let resto = +(total - base * n).toFixed(2);
                for (let i = 0; i < n; i++) {
                    let v = base;
                    if (resto > 0) {
                        v += 0.01;
                        resto = +(resto - 0.01).toFixed(2);
                    }
                    valores.push(v.toFixed(2));
                }
            }

            const fechas = $list.querySelectorAll('.cuotas-fecha');
            const montos = $list.querySelectorAll('.cuotas-valor');
            for (let i = 0; i < n; i++) {
                if (fBase) fechas[i].value = addMonthsToISO(fBase, i);
                if (!isNaN(valores[i])) montos[i].value = valores[i];
            }
        }

        // Eventos
        $fa.addEventListener('change', recalcSiguientePago);
        $pl.addEventListener('input', recalcSiguientePago);
        $nc.addEventListener('input', syncCuotasCount);
        $btnDist.addEventListener('click', distribuir);

        // Inicial
        recalcSiguientePago();
        // Si no hay filas (contrato nuevo), crea tantas como NUMERO_CUOTAS
        if ($list.querySelectorAll('.cuota-item').length === 0) {
            syncCuotasCount();
        } else {
            // Si ya habían filas cargadas, asegúrate de que # coincida con NUMERO_CUOTAS
            syncCuotasCount();
        }
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

        .compact-ui .form-control,
        {
            padding: .25rem .5rem;
            font-size: .92rem;
            line-height: 1.25;
            height: calc(1.25rem + .5rem + 2px);
            /* altura visual más baja */
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


@endsection
