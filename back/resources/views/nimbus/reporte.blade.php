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
                    <!-- Debajo del input Fecha -->
                    <div class="col-auto">
                        <label class="form-label mb-0">Buscar placa</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="filtroPlaca" class="form-control" placeholder="ABC1234, (01/2345)">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiarFiltro"
                                title="Limpiar">×</button>
                        </div>
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
                                            id="tabla-{{ $ruta['idRoute'] }}" data-stops='@json($stops, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)'
                                            data-tarifas='@json(
                                                $ruta['tarifas'] ?? new \stdClass(),
                                                JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)'>

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
                                                            {{ rutinaFrom($plan) }}
                                                        </td>
                                                        <td class="sticky-col sticky-sancion text-end">
                                                            <span class="sancion-amount" data-total="0.00">$0.00</span>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 ms-2 ver-sancion">Ver</button>
                                                        </td>
                                                        @for ($j = 0; $j < count($stops); $j++)
                                                            <td class="text-center col-plan text-nowrap">
                                                                {{ $plan[$j] ?? '--:--' }}</td>
                                                            <td class="text-center col-eje text-nowrap">
                                                                {{ $ejec[$j] ?? '--:--' }}
                                                            </td>
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
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content sancion-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">Detalle de sanción</h5>
                        <div class="small text-muted">
                            <span class="me-2"><i class="bi bi-calendar3 me-1"></i><span id="sm-fecha">—</span></span>
                            <span class="me-2"><i class="bi bi-signpost-2 me-1"></i><span
                                    id="sm-ruta">—</span></span>
                            <span class="me-2"><i class="bi bi-truck-front me-1"></i><span
                                    id="sm-placa">—</span></span>
                        </div>
                    </div>

                    <div class="text-end ms-auto">
                        <div class="label text-uppercase small text-muted">Total</div>
                        <div id="sm-total" class="display-6 fw-bold text-success mb-0">$0.00</div>
                    </div>
                </div>

                <div class="modal-subheader bg-light rounded px-3 py-2 mx-3 mb-2">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="stat-chip chip-caidas" title="Geocercas con caída (Dif < 0)">Caídas: <b
                                id="sm-caidas">0</b></span>
                        <span class="stat-chip chip-atiempo" title="Geocercas a tiempo (Dif = 0)">A tiempo: <b
                                id="sm-atiempo">0</b></span>
                        <span class="stat-chip chip-adelanto" title="Geocercas con adelanto (Dif > 0)">Adelanto: <b
                                id="sm-adelanto">0</b></span>
                        <span class="stat-chip chip-renglones" title="Total de renglones">Geocercas: <b
                                id="sm-rows">0</b></span>
                    </div>
                </div>

                <div class="modal-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle sancion-table mb-0">
                            <thead class="table-light sticky-top shadow-sm">
                                <tr>
                                    <th class="text-center" style="width:50px">#</th>
                                    <th>Geocerca</th>
                                    <th class="text-center" style="width:90px">Dif</th>
                                    <th class="text-end mono" style="width:120px">Tarifa</th>
                                    <th class="text-end mono" style="width:140px">Cargo</th>
                                </tr>
                            </thead>
                            <tbody id="detSancionBody"><!-- filas se inyectan en tu JS --></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer justify-content-between sticky-footer">
                    <div class="small text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Los valores en <b>Dif</b> negativos generan cargo. Los positivos son informativos.
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-primary" id="btnPrintSancion">Imprimir A4</button>
                        <button class="btn btn-outline-primary" id="btnTicketSancion">Imprimir ticket</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="{{ asset('js/reporte-dia-all.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>

    <script>
        (function() {
            // ======= Configura aquí el nombre EXACTO de la impresora =======
            const PRINTER_NAME = 'PRUEBA2'; // cámbialo por el nombre que ves en Windows

            // Firmas mínimas (modo sin certificados propios)
            qz.security.setCertificatePromise(function(resolve, reject) {
                resolve("-----BEGIN CERTIFICATE-----\nMIIB...TU_CERT...\n-----END CERTIFICATE-----");
            });
            qz.security.setSignaturePromise(function(toSign) {
                return function(resolve, reject) {
                    resolve(null);
                };
            });

            // Helper: conecta QZ
            async function qzConnect() {
                // Ya activo
                if (qz.websocket.isActive()) return;

                try {
                    await qz.websocket.connect({
                        retries: 2, // intenta un par de veces
                        delay: 0.5 // segundos entre intentos
                    });
                } catch (e) {
                    // Mensaje claro al usuario
                    throw new Error(
                        "No se pudo conectar con QZ Tray.\n\n" +
                        "1) Verifica que QZ Tray esté abierto (icono verde).\n" +
                        "2) Si aparece un cuadro pidiendo permisos, presiona Aceptar.\n" +
                        "3) En QZ → Connections/Whitelist, borra entradas antiguas y vuelve a intentar.\n\n" +
                        "Detalle técnico: " + (e?.message || e)
                    );
                }
            }


            // Helper: busca la impresora
            async function getPrinter() {
                const list = await qz.printers.find(PRINTER_NAME);
                return list || PRINTER_NAME;
            }

            function buildEscPosTicket({
                empresa,
                fecha,
                ruta,
                placa,
                total,
                caidas,
                rows
            }) {
                const ESC = '\x1B',
                    GS = '\x1D',
                    SI = '\x0F',
                    DC2 = '\x12';

                const init = ESC + '@';
                const align = (n) => ESC + 'a' + String.fromCharCode(n); // 0=L,1=C,2=R
                const boldOn = ESC + 'E' + '\x01',
                    boldOff = ESC + 'E' + '\x00';
                const fontB = ESC + 'M' + '\x01'; // angosto
                const normal = ESC + '!' + '\x00';
                const hr = (w = 33) => '-'.repeat(w) + '\n';

                // Alimentar n líneas
                const feed = (n) => ESC + 'd' + String.fromCharCode(n & 0xFF);
                // Corte recomendado para Epson: GS V 66 0 (full cut) — si no hay cortador se ignora
                const cut = GS + 'V' + '\x42' + '\x00';

                // Layout de columnas (42 col): " 2idx + 1sp + 22name + 1sp + 3dif + 1sp + 5tar + 1sp + 7cargo = 43?
                // Ajustamos a EXACTO 42: 2 + 1 + 21 + 1 + 3 + 1 + 5 + 1 + 7 = 42
                const NAME_W = 15;

                function lineItem(idx, name, dif, cargo) {
                    const nm = fitNameOneLine(name, NAME_W); // 26 cols para nombre
                    const dff = fmtDiff(dif).padStart(3, ' '); // 3 cols para Dif
                    const cStr = money(cargo).replace('$', '').padStart(8, ' '); // 8 cols para Cargo
                    // 2(idx) + 1 + 26(name) + 1 + 3(dif) + 1 + 8(cargo) = 42 columnas exactas
                    return `${String(idx).padStart(2,' ')} ${nm} ${dff} ${cStr}\n`;
                }

                let out = init + fontB + normal;
                out += align(1) + boldOn + (empresa || 'EMPRESA') + '\n' + boldOff;
                out += 'SANCION DE MINUTOS CAIDOS\n';
                out += align(0);
                out += `Fecha: ${fecha || '--'}\n`;
                out += `Ruta : ${ruta  || '--'}\n`;
                out += `Placa: ${placa || '--'}\n`;
                out += hr();

                // Cuerpo compacto (condensed ON)
                out += SI;
                out += ` # ${'Geocerca'.padEnd(NAME_W,' ')} ${'Dif'.padStart(3,' ')} ${'Cargo'.padStart(8,' ')}\n`;

                out += hr();

                (rows || []).forEach(r => {
                    out += lineItem(r.idx, r.n || '', r.dif, r.cargo);
                });


                // condensed OFF
                out += DC2;
                out += hr();

                // Resumen (alineado a la derecha)
                out += align(2) + `Geocercas con caida: ${caidas || 0}\n`;
                out += boldOn + align(2) + `TOTAL: $${money(total)}\n` + boldOff;

                // Alimenta un poco y CORTA DESPUÉS DEL TOTAL
                out += feed(1) + cut;

                return out;
            }

            function toAscii(s) {
                try {
                    return String(s || '')
                        .normalize('NFD') // separa tildes
                        .replace(/[\u0300-\u036f]/g, '') // quita tildes
                        .replace(/[^\x20-\x7E]/g, ''); // deja solo ASCII imprimible
                } catch {
                    return String(s || '');
                }
            }

            function fitNameOneLine(name, width) {
                let s = String(name || '')
                    .replace(/^(?:\s*\d+\s*\.\s*)+/, '') // quita "12. " al inicio (1 o más veces)
                    .replace(/\s+/g, ' ') // colapsa espacios
                    .trim();

                s = toAscii(s);

                if (s.length <= width) return s.padEnd(width, ' ');

                // si no cabe, deja sitio para '...' (3).
                const w = Math.max(3, width);
                return (s.slice(0, w - 3) + '...').padEnd(width, ' ');
            }

            function money(n) {
                n = parseFloat(n || 0);
                return n.toFixed(2);
            }

            function stripNumPrefix(name) {
                return String(name || '').replace(/^(?:\s*\d+\s*\.\s*)+/, '');
            }
            // Si no hay diferencia => "-"
            function fmtDiff(s) {
                s = String(s || '').trim();
                if (!s) return '-';
                const m = s.match(/[+\-]?\d+/);
                if (!m) return '-';
                const v = parseInt(m[0], 10);
                if (isNaN(v)) return '-';
                return (m[0].startsWith('+') ? ('+' + Math.abs(v)) : String(v));
            }
            // Compacta el nombre para que quepa EXACTAMENTE en 'width' columnas monoespaciadas
            function squeezeName(name, width) {
                name = String(name || '');

                // Quita uno o varios prefijos numerados: "12. " o "12. 12. "
                name = name.replace(/^(?:\s*\d+\s*\.\s*)+/, '');

                // Normaliza espacios
                name = name.replace(/\s+/g, ' ').trim();

                if (name.length <= width) return name.padEnd(width, ' ');

                const words = name.split(' ');
                let out = '';

                for (let i = 0; i < words.length; i++) {
                    let w = words[i];
                    if (out.length === 0) {
                        if (w.length > width) {
                            // La 1ª palabra sola ya no cabe: recórtala (mínimo 3)
                            return w.slice(0, Math.max(3, width)).padEnd(width, ' ');
                        }
                        out = w;
                        continue;
                    }
                    // +1 por el espacio a insertar
                    if (out.length + 1 + w.length <= width) {
                        out += ' ' + w;
                    } else {
                        const remain = width - out.length - 1; // lo que queda para la última palabra
                        if (remain > 0) {
                            out += ' ' + w.slice(0, Math.max(3, remain)); // corta a mínimo 3
                        }
                        return out.padEnd(width, ' ');
                    }
                }
                // Si caben todas, rellena a la derecha
                return out.slice(0, width).padEnd(width, ' ');
            }


            // Lee los datos del modal (los dejamos listos en tu JS)
            function getModalData() {
                const modal = document.getElementById('modalSancion');
                const empresa = modal.dataset.empresa || '';
                const fecha = modal.dataset.fecha || '';
                const ruta = modal.dataset.ruta || '';
                const placa = modal.dataset.placa || '';
                const total = modal.dataset.total || '0.00';
                const caidas = parseInt(modal.dataset.caidas || '0', 10) || 0;

                // Reconstruimos las filas en formato compacto
                const rows = [];
                document.querySelectorAll('#detSancionBody tr').forEach((tr, i) => {
                    const tds = tr.querySelectorAll('td');
                    if (tds.length < 5) return;
                    rows.push({
                        idx: i + 1,
                        n: (tds[1]?.textContent || '').trim(),
                        dif: (tds[2]?.textContent || '').trim(),
                        tarifa: parseFloat((tds[3]?.textContent || '').replace(/[^\d.]/g, '')) || 0,
                        cargo: parseFloat((tds[4]?.textContent || '').replace(/[^\d.]/g, '')) || 0,
                    });
                });

                return {
                    empresa,
                    fecha,
                    ruta,
                    placa,
                    total,
                    caidas,
                    rows
                };
            }

            // Click “Imprimir ticket”
            document.addEventListener('click', async (e) => {
                if (!e.target.closest('#btnTicketSancion')) return;
                try {
                    await qzConnect();
                    const printer = await getPrinter();
                    const data = getModalData();
                    const escpos = buildEscPosTicket(data);

                    const cfg = qz.configs.create(printer, {
                        encoding: 'iso-8859-1'
                    }); // TM-U220 va bien con latin
                    await qz.print(cfg, [escpos]);
                } catch (err) {
                    console.error(err);
                    alert('No se pudo imprimir el ticket. Ver consola.');
                }
            });
        })();
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

        /* ===== Modal de sanción (mejora visual) ===== */
        .sancion-modal .modal-header .display-6 {
            line-height: 1;
        }

        .sancion-modal .modal-subheader {
            border: 1px dashed rgba(0, 0, 0, .08);
        }

        .stat-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .6rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: .78rem;
            border: 1px solid transparent;
        }

        .chip-caidas {
            background: #fde8e8;
            color: #b42318;
            border-color: #fac5c5;
        }

        .chip-adelanto {
            background: #e8f7ee;
            color: #0f7a3a;
            border-color: #c6edd6;
        }

        .chip-atiempo {
            background: #eef2ff;
            color: #3730a3;
            border-color: #d9e1ff;
        }

        .chip-renglones {
            background: #f3f4f6;
            color: #374151;
            border-color: #e5e7eb;
        }

        .sancion-table tbody tr:hover {
            background: #fafafa;
        }

        .sancion-table td,
        .sancion-table th {
            padding: .5rem .6rem;
        }

        .sancion-table .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
        }

        .badge-dif {
            display: inline-block;
            min-width: 2.4rem;
            text-align: center;
            font-weight: 700;
            padding: .15rem .35rem;
            border-radius: .375rem;
        }

        .badge-dif.neg {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-dif.zero {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-dif.pos {
            background: #dcfce7;
            color: #166534;
        }

        .money {
            font-variant-numeric: tabular-nums;
        }

        /* Footer pegajoso suave en el modal */
        .sticky-footer {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid rgba(0, 0, 0, .075);
            box-shadow: 0 -4px 12px rgba(0, 0, 0, .03);
        }
    </style>
    <script>
        (function() {
            const $modal = document.getElementById('modalSancion');
            const $body = document.getElementById('detSancionBody');

            // Utilidades
            const money = n => (parseFloat(n || 0)).toFixed(2);
            const parseMoney = s => parseFloat(String(s || '').replace(/[^\d.-]/g, '') || 0);

            function badgeDif(raw) {
                const txt = String(raw || '').trim();

                // Si viene sin dígitos y es solo un guion (o variantes), muéstralo como "-"
                if (/^[\-–—]$/.test(txt)) {
                    return `<span class="badge-dif zero">-</span>`;
                }

                const m = txt.match(/[+\-]?\d+/);
                if (!m) {
                    // Sin número: también renderiza "-" y trátalo visualmente como "a tiempo"
                    return `<span class="badge-dif zero">-</span>`;
                }

                const v = parseInt(m[0], 10);
                const cls = v < 0 ? 'neg' : v > 0 ? 'pos' : 'zero';
                const label = v > 0 ? `+${v}` : String(v);
                return `<span class="badge-dif ${cls}">${label}</span>`;
            }


            function refreshHeaderFromDataset(ds) {
                // dataset viene de tu código que llena el modal antes de mostrarlo
                document.getElementById('sm-fecha').textContent = ds.fecha || '—';
                document.getElementById('sm-ruta').textContent = ds.ruta || '—';
                document.getElementById('sm-placa').textContent = ds.placa || '—';
            }

            function recomputeCountersAndPaint() {
                let caidas = 0,
                    aTiempo = 0,
                    adelanto = 0,
                    total = 0,
                    rows = 0;

                // Re-pinta filas
                $body.querySelectorAll('tr').forEach(tr => {
                    const tds = tr.querySelectorAll('td');
                    if (tds.length < 5) return;

                    // Dif -> chip
                    const difTd = tds[2];
                    const difRaw = difTd.textContent.trim();
                    difTd.innerHTML = badgeDif(difRaw);

                    // Contadores
                    const v = parseInt(String(difRaw || '').match(/[+-]?\d+/)?.[0] ?? '0', 10);
                    if (!isNaN(v)) {
                        if (v < 0) caidas++;
                        else if (v > 0) adelanto++;
                        else aTiempo++;
                    }

                    // Cargo -> formateo
                    const cargoTd = tds[4];
                    const cargo = parseMoney(cargoTd.textContent);
                    total += cargo;
                    cargoTd.innerHTML =
                        `<span class="money ${cargo>0?'fw-semibold text-danger':''}">$${money(cargo)}</span>`;

                    // Tarifa -> normaliza
                    const tarifaTd = tds[3];
                    const tarifa = parseMoney(tarifaTd.textContent);
                    tarifaTd.innerHTML = `<span class="money">$${money(tarifa)}</span>`;

                    rows++;
                });

                // Pinta contadores y total
                document.getElementById('sm-caidas').textContent = caidas;
                document.getElementById('sm-adelanto').textContent = adelanto;
                document.getElementById('sm-atiempo').textContent = aTiempo;
                document.getElementById('sm-rows').textContent = rows;
                document.getElementById('sm-total').textContent = '$' + money(total);
            }

            // Cada vez que se muestra el modal, actualizamos header y pintamos filas
            $modal.addEventListener('shown.bs.modal', () => {
                const ds = Object.assign({}, $modal.dataset); // espera que tú seteas data-* en el modal
                refreshHeaderFromDataset(ds);
                recomputeCountersAndPaint();
            });
        })();



        // ------------- Filtro por placa -------------
        const $filtroPlaca = document.getElementById("filtroPlaca");
        const $btnLimpiarFiltro = document.getElementById("btnLimpiarFiltro");

        function getActiveTable() {
            const activePane = document.querySelector(".route-table:not(.d-none)");
            if (!activePane) return null;
            return activePane.querySelector("table.table-compact");
        }

        function normaliza(s) {
            return String(s || "")
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // sin tildes
                .toUpperCase();
        }

        // Permite múltiples términos, todos deben aparecer (AND)
        function coincidePlaca(textoPlaca, query) {
            const base = normaliza(textoPlaca);
            const terms = normaliza(query).split(/\s+/).filter(Boolean);
            for (const t of terms) {
                if (!base.includes(t)) return false;
            }
            return true;
        }

        function aplicarFiltroPlaca(q) {
            const table = getActiveTable();
            if (!table) return;

            let visibles = 0;
            const rows = table.querySelectorAll("tbody tr");
            rows.forEach(tr => {
                const placaCell = tr.querySelector(".sticky-placa"); // toma TODO (código + paréntesis)
                const textoPlaca = placaCell ? placaCell.innerText.replace(/\s+/g, " ").trim() : "";
                const show = !q || coincidePlaca(textoPlaca, q);
                tr.style.display = show ? "" : "none";
                if (show) visibles++;
            });

            // (Opcional) mensaje si no hay resultados
            let msg = table._noResEl;
            if (!msg) {
                msg = document.createElement("div");
                msg.className = "alert alert-info py-1 px-2 small mt-2";
                msg.textContent = "No hay coincidencias para la placa buscada.";
                msg.style.display = "none";
                table.parentElement.appendChild(msg);
                table._noResEl = msg;
            }
            msg.style.display = (visibles === 0) ? "" : "none";
        }

        // Eventos
        if ($filtroPlaca) {
            $filtroPlaca.addEventListener("input", (e) => aplicarFiltroPlaca(e.target.value));
        }
        if ($btnLimpiarFiltro) {
            $btnLimpiarFiltro.addEventListener("click", () => {
                $filtroPlaca.value = "";
                aplicarFiltroPlaca("");
                $filtroPlaca.focus();
            });
        }

        // Reaplicar filtro al cambiar de pestaña (rutas)
        document.addEventListener("click", (e) => {
            if (!e.target.closest(".route-btn")) return;
            // da un pequeño tiempo a que se muestre la tabla
            setTimeout(() => aplicarFiltroPlaca($filtroPlaca?.value || ""), 0);
        });
    </script>


@endsection


@section('jsCode', 'js/scriptNavBar.js')
