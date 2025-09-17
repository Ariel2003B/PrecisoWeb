@extends('layout')

@section('Titulo', 'Minutos Caídos')

@section('content')
    @php
        // Helpers de presentación
        function shortStop($name)
        {
            return \Illuminate\Support\Str::limit($name ?? 'Parada', 12);
        }
        function rutinaFrom($arr)
        {
            if (!is_array($arr) || empty($arr)) {
                return '—';
            }
            $first = $arr[0] ?? '--:--';
            $last = $arr[count($arr) - 1] ?? '--:--';
            return ($first ?: '--:--') . ' - ' . ($last ?: '--:--');
        }
        function difClass($v)
        {
            if ($v === null || $v === '') {
                return 'text-muted';
            }
            if ((int) $v < 0) {
                return 'text-danger';
            }
            if ((int) $v > 0) {
                return 'text-success';
            }
            return 'text-secondary';
        }
        function placa($nombreUnidad)
        {
            // "PUC0240 (31/1250)" => ["PUC0240", "(31/1250)"]
            if (preg_match('/^([A-Z0-9]+)\s*(\([^)]+\))?/i', (string) $nombreUnidad, $m)) {
                return [trim($m[1] ?? ''), trim($m[2] ?? '')];
            }
            return [$nombreUnidad, ''];
        }
    @endphp

    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Minutos Caídos</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current">Reporte día - Todas las unidades</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div id="printMeta" data-empresa="{{ $empresa->NOMBRE }}" data-fecha="{{ $fecha }}"></div>

            <div class="container">
                {{-- Filtros rápidos --}}
                <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/nimbus/reporte-dia-all') }}">
                    <div class="col-auto">
                        <label class="form-label mb-0">Fecha</label>
                        <input type="date" name="fecha" value="{{ $fecha }}"
                            class="form-control form-control-sm">
                    </div>
                    {{-- <div class="col-auto">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="toggleWrapStops">
                            <label class="form-check-label" for="toggleWrapStops">Nombres completos</label>
                        </div>
                    </div> --}}

                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm">Actualizar</button>
                    </div>
                    <div class="col-auto ms-auto">
                        <span class="badge bg-secondary">Empresa: {{ $empresa->NOMBRE ?? '—' }}</span>

                    </div>
                </form>

                @isset($error)
                    <div class="alert alert-danger">{{ $error }}</div>
                @endisset

                @if (empty($rutas))
                    <div class="alert alert-warning">No hay datos para mostrar.</div>
                @else
                    <div class="row">
                        {{-- Sidebar de rutas --}}
                        <div class="col-12 col-md-3 col-lg-2 mb-3">
                            <div class="list-group" id="rutasList">
                                @foreach ($rutas as $i => $ruta)
                                    <button type="button"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center route-btn @if ($i === 0) active @endif"
                                        data-target="#route-{{ $ruta['idRoute'] }}">
                                        {{ $ruta['nombre'] ?? 'Ruta ' . $ruta['idRoute'] }}
                                        <span class="badge bg-secondary">{{ count($ruta['data'] ?? []) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Contenido de cada ruta --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            @foreach ($rutas as $i => $ruta)
                                @php
                                    $stops = $ruta['stops'] ?? [];
                                    $vueltas = $ruta['data'] ?? [];

                                    // las tarifas ya vienen del controlador
                                    $tarifasRoute = (array) ($ruta['tarifas'] ?? []);
                                    // JSON seguro: llaves string => número
                                    $tarifasJson = json_encode($tarifasRoute, JSON_UNESCAPED_UNICODE);
                                @endphp

                                <div class="route-table @if ($i !== 0) d-none @endif"
                                    id="route-{{ $ruta['idRoute'] }}">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="mb-0 me-2">{{ $ruta['nombre'] ?? 'Ruta ' . $ruta['idRoute'] }}</h5>
                                        <span class="text-muted small">({{ count($vueltas) }} vueltas)</span>
                                    </div>
                                    {{-- @php
                                        // Si el controlador no envió tarifas, las armamos aquí:
                                        $tarifasRoute =
                                            $ruta['tarifas'] ??
                                            \App\Models\GeoStop::mapaTarifas(
                                                $empresa->EMP_ID,
                                                array_map(fn($s) => (int) ($s['id'] ?? 0), $stops),
                                            );
                                    @endphp --}}
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered align-middle table-compact"
                                            id="tabla-{{ $ruta['idRoute'] }}" data-stops='@json($stops)'
                                            data-tarifas='{{ $tarifasJson }}'>
                                            <thead class="table-light align-middle"
                                                style="position: sticky; top: 0; z-index: 10;">
                                                <tr>
                                                    <th class="col-index sticky-col sticky-index text-center">#</th>
                                                    <th class="col-placa sticky-col sticky-placa">PLACA</th>
                                                    <th class="col-rutina sticky-col sticky-rutina">RUTINA</th>
                                                    <th class="col-sancion sticky-col sticky-sancion text-end">Sanción (USD)
                                                    </th>

                                                    @foreach ($stops as $s)
                                                        @php $full = $s['n'] ?? 'Parada'; @endphp
                                                        <th class="text-center" colspan="3" title="{{ $full }}">
                                                            <span class="stop-title">{{ $full }}</span>
                                                        </th>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <th class="sticky-col sticky-index"></th>
                                                    <th class="sticky-col sticky-placa"></th>
                                                    <th class="sticky-col sticky-rutina"></th>
                                                    <th class="sticky-col sticky-sancion"></th>
                                                    @foreach ($stops as $s)
                                                        <th class="text-center col-plan">Plan.</th>
                                                        <th class="text-center col-eje">Eje.</th>
                                                        <th class="text-center col-dif">Dif</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($vueltas as $idx => $v)
                                                    @php
                                                        [$placaCode, $extra] = placa($v['nombreUnidad'] ?? '');
                                                        $plan = $v['horaProgramada'] ?? [];
                                                        $ejec = $v['horaEjecutada'] ?? [];
                                                        $dif = $v['diferencia'] ?? [];
                                                    @endphp
                                                    <tr>
                                                        <td class="sticky-col sticky-index text-center">{{ $idx + 1 }}
                                                        </td>
                                                        <td class="sticky-col sticky-placa">
                                                            <div class="fw-semibold text-nowrap">{{ $placaCode }}</div>
                                                            <div class="text-muted small">{{ $extra }}</div>
                                                        </td>
                                                        <td class="sticky-col sticky-rutina text-nowrap">
                                                            {{ rutinaFrom($plan) }}</td>
                                                        <td class="sticky-col sticky-sancion text-end">
                                                            <span class="sancion-amount" data-total="0.00">$0.00</span>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 ms-2 ver-sancion">Ver</button>
                                                        </td>
                                                        @for ($j = 0; $j < count($stops); $j++)
                                                            <td class="text-center col-plan text-nowrap">
                                                                {{ $plan[$j] ?? '--:--' }}</td>
                                                            <td class="text-center col-eje text-nowrap">
                                                                {{ $ejec[$j] ?? '--:--' }}</td>
                                                            @php $d = $dif[$j] ?? null; @endphp
                                                            <td class="text-center col-dif {{ difClass($d) }}">
                                                                {{ $d === null ? '—' : ($d > 0 ? '+' : '') . $d }}
                                                            </td>
                                                        @endfor
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </main>
    <div class="modal fade" id="modalSancion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de sanción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size:.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Geocerca</th>
                                    <th class="text-center" style="width:70px;">Dif</th>
                                    <th class="text-end" style="width:120px;">Tarifa</th>
                                    <th class="text-end" style="width:120px;">Cargo</th>
                                </tr>
                            </thead>
                            <tbody id="detSancionBody"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th class="text-end" id="detSancionTotal">$0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" id="btnPrintSancion">Imprimir</button> {{-- NUEVO --}}
                </div>

            </div>
        </div>
    </div>





    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // rutas
            const buttons = document.querySelectorAll('.route-btn');
            const tables = document.querySelectorAll('.route-table');

            function show(target) {
                tables.forEach(t => t.classList.add('d-none'));
                buttons.forEach(b => b.classList.remove('active'));
                const el = document.querySelector(target);
                if (el) el.classList.remove('d-none');
            }
            buttons.forEach(btn => {
                btn.addEventListener('click', function() {
                    show(this.dataset.target);
                    this.classList.add('active');
                });
            });

            // nombres completos on/off
            const wrapToggle = document.getElementById('toggleWrapStops');
            const container = document.querySelector('.container'); // o un wrapper más específico
            if (wrapToggle) {
                wrapToggle.addEventListener('change', function() {
                    if (this.checked) container.classList.add('wrap-stops');
                    else container.classList.remove('wrap-stops');
                });
            }

            // Bootstrap tooltip (si usas BS5)
            if (window.bootstrap) {
                const tts = [].slice.call(document.querySelectorAll('[title]'));
                tts.forEach(el => new bootstrap.Tooltip(el));
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // === Calcular totales al cargar ===
            document.querySelectorAll('table.table-compact').forEach(table => {
                const stops = safeJson(table.dataset.stops) || [];
                const tarifas = buildTarifaLookup(safeJson(table.dataset.tarifas));

                const rows = table.querySelectorAll('tbody tr');

                rows.forEach(tr => {
                    const difCells = tr.querySelectorAll('td.col-dif');
                    let total = 0;

                    difCells.forEach((td, idx) => {
                        const raw = (td.textContent || '').trim();
                        const diff = parseInt(raw.replace(/[^\-0-9]/g, ''), 10);
                        const stop = stops[idx] || {};
                        const nid = stop.id;
                        const t = parseFloat(tarifas[String(nid)] ?? tarifas[nid] ?? 0) ||
                            0;


                        if (!isNaN(diff) && diff < 0 && t > 0) {
                            total += Math.abs(diff) * t;
                        }
                    });

                    const amountEl = tr.querySelector('.sancion-amount');
                    if (amountEl) {
                        amountEl.dataset.total = total.toFixed(2);
                        amountEl.textContent = '$' + numberFmt(total);
                    }

                    // Guardamos el contexto en el <tr> para usar en "Ver"
                    tr._sancionContext = {
                        stops,
                        tarifas
                    };
                });
            });

            // === Botón "Ver" (detalle en modal) ===
            document.querySelectorAll('.ver-sancion').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tr = this.closest('tr');
                    const table = this.closest('table');
                    const stops = (tr?._sancionContext?.stops) || safeJson(table.dataset.stops) ||
                    [];
                    const tarifas = (tr?._sancionContext?.tarifas) || buildTarifaLookup(safeJson(
                        table.dataset.tarifas));

                    const placa = tr.querySelector('.sticky-placa .fw-semibold')?.textContent
                        ?.trim() || '';
                    const difTds = tr.querySelectorAll('td.col-dif');

                    let bodyHtml = '';
                    let total = 0;

                    difTds.forEach((td, idx) => {
                        const raw = (td.textContent || '').trim();
                        const diff = parseInt(raw.replace(/[^\-0-9]/g, ''),
                            10); // puede ser NaN
                        const stop = stops[idx] || {};
                        const nid = stop.id;
                        const name = stop.n || ('Parada ' + (idx + 1));
                        const t = parseFloat(tarifas[String(nid)] ?? tarifas[nid] ?? 0) ||
                            0;

                        const cargo = (!isNaN(diff) && diff < 0 && t > 0) ? Math.abs(diff) *
                            t : 0;

                        if (cargo > 0) total += cargo;

                        const cls = isNaN(diff) ? 'text-muted' :
                            diff < 0 ? 'text-danger fw-semibold' :
                            diff > 0 ? 'text-success' :
                            'text-secondary';

                        bodyHtml += `
          <tr>
            <td class="text-center">${idx+1}</td>
            <td>${escapeHtml(name)}</td>
            <td class="text-center ${cls}">${isNaN(diff) ? '—' : (diff>0? '+'+diff : diff)}</td>
            <td class="text-end">${t ? '$'+numberFmt(t) : '$0.00'}</td>
            <td class="text-end">${cargo ? '$'+numberFmt(cargo) : '$0.00'}</td>
          </tr>`;
                    });

                    document.getElementById('detSancionBody').innerHTML = bodyHtml || `
        <tr><td colspan="5" class="text-center text-muted">Sin datos</td></tr>`;
                    document.getElementById('detSancionTotal').textContent = '$' + numberFmt(total);

                    const modalEl = document.getElementById('modalSancion');
                    modalEl.querySelector('.modal-title').textContent =
                        `Detalle de sanción – ${placa}`;
                    if (window.bootstrap) new bootstrap.Modal(modalEl).show();
                });
            });

            // Helpers
            function safeJson(s) {
                try {
                    return JSON.parse(s || 'null');
                } catch {
                    return null;
                }
            }

            function numberFmt(n) {
                return (isNaN(n) ? 0 : n).toFixed(2);
            }

            function escapeHtml(s) {
                return (s || '').replace(/[&<>"']/g, m => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [m]));
            }
        });

        function buildTarifaLookup(raw) {
            const out = {};
            if (!raw) return out;

            // Si viene como objeto { "891516": 0.5, ... }
            if (typeof raw === 'object' && !Array.isArray(raw)) {
                Object.keys(raw).forEach(k => {
                    out[String(k)] = parseFloat(raw[k]) || 0;
                });
                return out;
            }

            // Si viniera como array de objetos [{id:891516, t:0.5}, ...] (por si algún día cambias)
            if (Array.isArray(raw)) {
                raw.forEach(x => {
                    if (x && (x.id !== undefined)) out[String(x.id)] = parseFloat(x.t ?? x.valor ?? x.rate ?? 0) ||
                        0;
                });
            }
            return out;
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ... (tu código existente de cálculo de totales)

            // === Botón "Ver" (detalle en modal) ===
            document.querySelectorAll('.ver-sancion').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tr = this.closest('tr');
                    const table = this.closest('table');
                    const contRT = this.closest('.route-table');
                    const stops = (tr?._sancionContext?.stops) || safeJson(table.dataset.stops) ||
                    [];
                    const tarifas = (tr?._sancionContext?.tarifas) || buildTarifaLookup(safeJson(
                        table.dataset.tarifas)) || {};

                    const placa = tr.querySelector('.sticky-placa .fw-semibold')?.textContent
                        ?.trim() || '';
                    const ruta = contRT?.querySelector('h5')?.textContent?.trim() || '';
                    const difTds = tr.querySelectorAll('td.col-dif');

                    let bodyHtmlAll = '';
                    let bodyHtmlCaidas = '';
                    let total = 0,
                        caidas = 0;

                    difTds.forEach((td, idx) => {
                        const raw = (td.textContent || '').trim();
                        const diff = parseInt(raw.replace(/[^\-0-9]/g, ''),
                            10); // puede ser NaN
                        const stop = stops[idx] || {};
                        const nid = stop.id;
                        const name = stop.n || ('Parada ' + (idx + 1));
                        const t = parseFloat(tarifas[String(nid)] ?? tarifas[nid] ?? 0) ||
                            0;

                        const cargo = (!isNaN(diff) && diff < 0 && t > 0) ? Math.abs(diff) *
                            t : 0;

                        if (cargo > 0) {
                            total += cargo;
                            caidas++;
                        }

                        const cls = isNaN(diff) ? 'text-muted' :
                            diff < 0 ? 'text-danger fw-semibold' :
                            diff > 0 ? 'text-success' :
                            'text-secondary';

                        const rowHtml = `
          <tr>
            <td class="text-center">${idx+1}</td>
            <td>${escapeHtml(name)}</td>
            <td class="text-center ${cls}">${isNaN(diff) ? '—' : (diff>0? '+'+diff : diff)}</td>
            <td class="text-end">${t ? '$'+numberFmt(t) : '$0.00'}</td>
            <td class="text-end">${cargo ? '$'+numberFmt(cargo) : '$0.00'}</td>
          </tr>`;

                        bodyHtmlAll += rowHtml;
                        if (cargo > 0) bodyHtmlCaidas += rowHtml;
                    });

                    // Pinta el detalle en el modal (todas las paradas)
                    document.getElementById('detSancionBody').innerHTML =
                        bodyHtmlAll ||
                        `<tr><td colspan="5" class="text-center text-muted">Sin datos</td></tr>`;
                    document.getElementById('detSancionTotal').textContent = '$' + numberFmt(total);

                    // Setea título del modal
                    const modalEl = document.getElementById('modalSancion');
                    modalEl.querySelector('.modal-title').textContent =
                        `Detalle de sanción – ${placa}`;

                    // === Guarda datos para impresión en data-attributes del modal ===
                    const meta = document.getElementById('printMeta');
                    modalEl.dataset.empresa = meta?.dataset.empresa || '';
                    modalEl.dataset.fecha = meta?.dataset.fecha || '';
                    modalEl.dataset.ruta = ruta;
                    modalEl.dataset.placa = placa;
                    modalEl.dataset.total = numberFmt(total);
                    modalEl.dataset.caidas = caidas.toString();
                    modalEl.dataset.rowsAll = bodyHtmlAll;
                    modalEl.dataset.rowsCaidas = bodyHtmlCaidas; // solo caídas

                    if (window.bootstrap) new bootstrap.Modal(modalEl).show();
                });
            });

            // === Botón "Imprimir" ===
            // === Botón "Imprimir" ===
            const btnPrint = document.getElementById('btnPrintSancion');
            if (btnPrint) {
                btnPrint.addEventListener('click', function() {
                    const modalEl = document.getElementById('modalSancion');
                    if (!modalEl) return;

                    const doPrint = () => {
                        safetyCleanModalArtifacts(); // limpiar backdrop/clases por si acaso
                        printSancion(modalEl); // abrir y mandar a imprimir
                    };

                    // Si está Bootstrap, espera a que el modal quede oculto (sin backdrop)
                    if (window.bootstrap) {
                        const inst = bootstrap.Modal.getInstance(modalEl);
                        modalEl.addEventListener('hidden.bs.modal', function onHidden() {
                            modalEl.removeEventListener('hidden.bs.modal', onHidden);
                            doPrint();
                        }, {
                            once: true
                        });
                        inst?.hide();
                    } else {
                        doPrint();
                    }
                });
            }


            // Helpers ya existentes...
            function safeJson(s) {
                try {
                    return JSON.parse(s || 'null');
                } catch {
                    return null;
                }
            }

            function numberFmt(n) {
                return (isNaN(n) ? 0 : n).toFixed(2);
            }

            function escapeHtml(s) {
                return (s || '').replace(/[&<>"']/g, m => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [m]));
            }

            function buildTarifaLookup(raw) {
                const out = {};
                if (!raw) return out;
                if (typeof raw === 'object' && !Array.isArray(raw)) {
                    Object.keys(raw).forEach(k => out[String(k)] = parseFloat(raw[k]) || 0);
                    return out;
                }
                if (Array.isArray(raw)) {
                    raw.forEach(x => {
                        if (x && (x.id !== undefined)) out[String(x.id)] = parseFloat(x.t ?? x.valor ?? x
                            .rate ?? 0) || 0;
                    });
                }
                return out;
            }

            // === Generador de impresión ===
            function safetyCleanModalArtifacts() {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
            }

            function printSancion(modalEl) {
                if (!modalEl) return;
                const empresa = modalEl.dataset.empresa || '';
                const fecha = modalEl.dataset.fecha || '';
                const ruta = modalEl.dataset.ruta || '';
                const placa = modalEl.dataset.placa || '';
                const total = modalEl.dataset.total || '0.00';
                const caidas = parseInt(modalEl.dataset.caidas || '0', 10);
                // tomamos SIEMPRE todas las filas:
                const rowsAll = modalEl.dataset.rowsAll || '';

                const html = `<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sanción ${escapeHtml(placa)} - ${escapeHtml(fecha)}</title>
  <style>
    @page { size: A4; margin: 18mm 14mm; }
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif; color:#0f172a; }
    h1 { font-size: 18px; margin:0 0 8px; }
    h2 { font-size: 15px; margin:14px 0 6px; }
    .muted { color:#64748b; }
    .badge { display:inline-block; padding:2px 8px; border-radius:999px; background:#eef2ff; color:#3730a3; font-size:12px; }
    .resume { margin:12px 0 16px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px; }
    .resume strong { font-size:18px; }
    table { width:100%; border-collapse:collapse; }
    th,td { border:1px solid #e2e8f0; padding:6px 8px; font-size:12px; }
    thead th { background:#f8fafc; text-align:left; }
    .text-end { text-align:right; }
    .text-center { text-align:center; }
    .small { font-size:11px; }
    .foot { margin-top:18px; font-size:11px; color:#64748b; }
  </style>
</head>
<body>
  <h1>Reporte de sanción <span class="badge">${escapeHtml(empresa)}</span></h1>
  <div class="small muted">Fecha: ${escapeHtml(fecha)} · Ruta: ${escapeHtml(ruta)} · Unidad: ${escapeHtml(placa)}</div>

  <div class="resume">
    <div><span class="muted">Geocercas con caída:</span> <strong>${caidas}</strong></div>
    <div><span class="muted">Total a pagar:</span> <strong>$${total}</strong></div>
  </div>

  <h2>Detalle (todas las geocercas)</h2>
  <table>
    <thead>
      <tr>
        <th style="width:40px;" class="text-center">#</th>
        <th>Geocerca</th>
        <th style="width:70px;" class="text-center">Dif</th>
        <th style="width:110px;" class="text-end">Tarifa</th>
        <th style="width:110px;" class="text-end">Cargo</th>
      </tr>
    </thead>
    <tbody>
      ${rowsAll}
    </tbody>
    <tfoot>
      <tr>
        <th colspan="4" class="text-end">Total</th>
        <th class="text-end">$${total}</th>
      </tr>
    </tfoot>
  </table>

  <div class="foot">Generado automáticamente · ${new Date().toLocaleString()}</div>
</body>
</html>`;

                const w = window.open('', '_blank');
                if (!w) return;
                w.document.open();
                w.document.write(html);
                w.document.close();
                w.addEventListener('load', () => {
                    w.focus();
                    w.print();
                });
                w.onafterprint = () => { try { w.close(); } catch(e) {} };

            }


        });
    </script>

    <style>
        /* ===== Tamaños más compactos ===== */
        :root {
            --w-index: 36px;
            --w-placa: 140px;
            --w-rutina: 120px;
            --w-plan: 54px;
            --w-eje: 54px;
            --w-dif: 36px;
            --w-sancion: 120px;

        }

        .table-compact {
            font-size: .74rem;
            /* más pequeño */
            line-height: 1.05;
        }

        .table-compact th,
        .table-compact td {
            padding: .18rem .25rem !important;
            /* menos alto y menos ancho */
        }

        /* Monoespaciado para horas/dif y que alineen mejor los dígitos */
        .table-compact .col-plan,
        .table-compact .col-eje,
        .table-compact .col-dif {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            letter-spacing: .2px;
        }

        /* ===== Columnas pegajosas (fijas) a la izquierda ===== */
        .sticky-col {
            position: sticky;
            z-index: 3;
            background: #fff !important;
            /* SIEMPRE blanco para que no se pinten raras */
            color: #111 !important;
            /* texto oscuro y legible */
        }

        thead .sticky-col {
            z-index: 12;
        }

        .col-index {
            min-width: var(--w-index);
            width: var(--w-index);
            text-align: center;
        }

        .col-placa {
            min-width: var(--w-placa);
            width: var(--w-placa);
        }

        .col-rutina {
            min-width: var(--w-rutina);
            width: var(--w-rutina);
        }

        .sticky-index {
            left: 0;
        }

        .col-sancion {
            min-width: var(--w-sancion);
            width: var(--w-sancion);
        }

        .sticky-sancion {
            left: calc(var(--w-index) + var(--w-placa) + var(--w-rutina));
            box-shadow: 2px 0 0 rgba(0, 0, 0, .04);
        }

        .sticky-placa {
            left: var(--w-index);
        }

        .sticky-rutina {
            left: calc(var(--w-index) + var(--w-placa));
        }

        /* sombras muy sutiles entre columnas fijas y el resto */
        .sticky-index,
        .sticky-placa,
        .sticky-rutina {
            box-shadow: 2px 0 0 rgba(0, 0, 0, .04);
        }

        .col-plan {
            min-width: var(--w-plan);
            width: var(--w-plan);
            text-align: center;
        }

        .col-eje {
            min-width: var(--w-eje);
            width: var(--w-eje);
            text-align: center;
        }

        .col-dif {
            min-width: var(--w-dif);
            width: var(--w-dif);
            text-align: center;
            font-weight: 600;
        }

        /* ===== Corregir interacción con .table-striped y hover ===== */
        .table-striped>tbody>tr:nth-of-type(odd) .sticky-col {
            background: #fff !important;
        }

        .table-hover>tbody>tr:hover .sticky-col {
            background: #f6f7f9 !important;
        }

        /* ===== Encabezados de paradas: 2 líneas + tooltip ===== */
        .stop-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* dos líneas visibles por defecto */
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 200px;
            /* un poco más ancho pero compacto */
            white-space: normal;
        }

        /* Si luego quieres ver todo el nombre, agrega la clase wrap-stops al contenedor */
        .wrap-stops .stop-title {
            -webkit-line-clamp: unset;
            max-width: none;
        }

        /* Head aún más compacto */
        thead.table-light th {
            font-size: .72rem;
            font-weight: 600;
        }

        /* Mejor contraste de “PLACA” en las celdas fijas */
        .sticky-col .fw-semibold {
            color: #0f172a !important;
        }

        /* azul gris oscuro */
        .sticky-col .small {
            color: #6b7280 !important;
        }

        /* gris medio */

        /* Colores de diferencia ya los manejas con tus helpers, solo aumentamos contraste leve */
        .text-danger {
            color: #c1121f !important;
        }

        .text-success {
            color: #127c36 !important;
        }

        .text-secondary {
            color: #5b5b5b !important;
        }

        .text-muted {
            color: #9aa0a6 !important;
        }
    </style>

@endsection


@section('jsCode', 'js/scriptNavBar.js')
