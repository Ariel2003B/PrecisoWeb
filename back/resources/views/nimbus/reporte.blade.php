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
            <div class="container d-lg-flex justify-content-between align-items-center px-2 px-md-3">
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

            <div class="container-fluid px-2 px-md-3">
                {{-- Filtros rápidos --}}
                <form class="row g-2 align-items-end position-relative mb-3" method="GET"
                    action="{{ url('/nimbus/reporte-dia-all') }}">
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

                        </div>
                    </div>

                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm">Actualizar</button>
                    </div>
                    <!-- Reloj Ecuador centrado -->
                    <div class="col clock-col d-none d-md-block">
                        <div id="relojEc" class="badge bg-danger text-white mono px-3 py-2">--:--:--</div>
                    </div>

                    <div class="col-auto ms-auto">
                        <span class="badge bg-warning">Empresa: {{ $empresa->NOMBRE ?? '—' }}</span>
                    </div>
                </form>

                @isset($error)
                    <div class="alert alert-danger">{{ $error }}</div>
                @endisset

                @if (empty($rutas))
                    <div class="alert alert-warning">No hay datos para mostrar.</div>
                @else
                    <div class="row">

                        <div class="routes-toolbar mb-2">
                            <div class="route-scroll nav nav-pills gap-2 flex-nowrap overflow-auto" id="rutasList"
                                style="white-space:nowrap">
                                @foreach ($rutas as $i => $ruta)
                                    <button type="button"
                                        class="route-btn btn btn-outline-danger btn-sm @if ($i === 0) active @endif"
                                        {{-- @if ($i === 0) style="background-color: #005298" @endif --}} data-target="#route-{{ $ruta['idRoute'] }}">
                                        {{ $ruta['nombre'] ?? 'Ruta ' . $ruta['idRoute'] }}

                                    </button>
                                @endforeach
                            </div>
                        </div>


                        {{-- Contenido de cada ruta --}}
                        <div class="tables-wrap">
                            @foreach ($rutas as $i => $ruta)
                                @php
                                    $stops = $ruta['stops'] ?? [];
                                    $vueltas = $ruta['data'] ?? [];
                                @endphp

                                <div class="route-table @if ($i !== 0) d-none @endif"
                                    id="route-{{ $ruta['idRoute'] }}">
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0 me-2 fw-semibold">
                                            {{ $ruta['nombre'] ?? 'Ruta ' . $ruta['idRoute'] }}</h6>
                                        <span class="text-muted small">({{ count($vueltas) }} vueltas)</span>
                                    </div>

                                    <div class="table-wrap">
                                        <div class="table-responsive table-scroller">
                                            <table
                                                class="table table-sm table-striped table-bordered align-middle table-compact row-hover"
                                                id="tabla-{{ $ruta['idRoute'] }}" data-stops='@json($stops, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)'
                                                data-tarifas='@json(
                                                    $ruta['tarifas'] ?? new \stdClass(),
                                                    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)'>


                                                <thead class="table-light align-middle sticky-top">
                                                    <tr>
                                                        <th rowspan="2"
                                                            class="col-index  sticky-col sticky-index  text-center align-middle">
                                                            #</th>
                                                        <th rowspan="2"
                                                            class="col-placa  sticky-col sticky-placa  text-center align-middle">
                                                            PLACA</th>
                                                        <th rowspan="2"
                                                            class="col-rutina sticky-col sticky-rutina text-center align-middle">
                                                            RUTINA</th>


                                                        @foreach ($stops as $s)
                                                            @php $full = $s['n'] ?? 'Parada'; @endphp
                                                            <th class="text-center" colspan="3"
                                                                title="{{ $full }}">
                                                                <span class="stop-title">{{ $full }}</span>
                                                            </th>
                                                        @endforeach

                                                        <th rowspan="2" class="col-adelantos text-center">Total adelantos
                                                        </th>
                                                        <th rowspan="2" class="col-atrasos text-center">Total atrasos
                                                        </th>
                                                        <th rowspan="2" class="col-sancion text-center">Sanción (USD)
                                                        </th>
                                                    </tr>
                                                    <tr>


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

                                                            // Totales por fila
                                                            $adelantos = 0; // suma de positivos
                                                            $atrasos = 0; // suma de negativos (en positivo)
                                                            foreach ($dif as $dv) {
                                                                if ($dv === null || $dv === '') {
                                                                    continue;
                                                                }
                                                                $n = (int) $dv;
                                                                if ($n > 0) {
                                                                    $adelantos += $n;
                                                                } elseif ($n < 0) {
                                                                    $atrasos += -$n;
                                                                }
                                                            }
                                                        @endphp

                                                        <tr data-idunidad="{{ (int) ($v['idUnidad'] ?? 0) }}"
                                                            data-routeid="{{ (int) ($ruta['idRoute'] ?? 0) }}">
                                                            <td class="sticky-col sticky-index text-center">
                                                                {{ $idx + 1 }}
                                                            </td>


                                                            <td class="sticky-col sticky-placa"
                                                                title="{{ trim($v['nombreUnidad'] ?? '') }}"
                                                                data-search="{{ trim(($placaCode ?? '') . ' ' . ($extra ?? '')) }}">
                                                                <div class="text-muted small text-nowrap">
                                                                    {{ $extra }}
                                                                </div>
                                                            </td>

                                                            <td class="sticky-col sticky-rutina text-nowrap">
                                                                {{ rutinaFrom($plan) }}</td>

                                                            @for ($j = 0; $j < count($stops); $j++)
                                                                <td class="text-center col-plan text-nowrap">
                                                                    {{ $plan[$j] ?? '--:--' }}</td>
                                                                <td class="text-center col-eje  text-nowrap">
                                                                    {{ $ejec[$j] ?? '--:--' }}</td>
                                                                @php $d = $dif[$j] ?? null; @endphp
                                                                <td class="text-center col-dif {{ difClass($d) }}">
                                                                    {{ $d === null ? '—' : ($d > 0 ? '+' : '') . $d }}
                                                                </td>
                                                            @endfor

                                                            {{-- NUEVO: totales --}}
                                                            <td class="text-center col-adelantos text-success fw-semibold">
                                                                +{{ $adelantos }}</td>
                                                            <td class="text-center col-atrasos   text-danger  fw-semibold">
                                                                {{ $atrasos }}</td>

                                                            {{-- Sanción al final, clickeable y pegada a la derecha --}}
                                                            <td class="text-center col-sancion">
                                                                <a href="javascript:void(0)"
                                                                    class="sancion-amount sancion-link ver-sancion"
                                                                    data-total="0.00" title="Ver detalle de sanción">
                                                                    $0.00
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach

                                                </tbody>
                                            </table>
                                        </div> {{-- .table-scroller --}}
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
            const PRINTER_NAME = 'TICKETSPRECISOGPS'; // cámbialo por el nombre que ves en Windows

            // Firmas mínimas (modo sin certificados propios)
            qz.security.setCertificatePromise(function(resolve, reject) {
                resolve(`-----BEGIN CERTIFICATE-----
MIIGfzCCBGegAwIBAgIRAKMjXP589Pf1XIuKZdMbGyswDQYJKoZIhvcNAQEMBQAw
SzELMAkGA1UEBhMCQVQxEDAOBgNVBAoTB1plcm9TU0wxKjAoBgNVBAMTIVplcm9T
U0wgUlNBIERvbWFpbiBTZWN1cmUgU2l0ZSBDQTAeFw0yNTA3MDIwMDAwMDBaFw0y
NTA5MzAyMzU5NTlaMBkxFzAVBgNVBAMTDnByZWNpc29ncHMuY29tMIIBIjANBgkq
hkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo6fJ3oPFBjRE25KpS+oOlofUBuE6xiSw
S2UX7bBDIx2teJYwjnb3vC77nM925qDn12ReQg7qTrn//9A0ogYUgmJpjBHBN75c
WkOxGvl60F5IMfQZEsbzNrMykYmoUP6m1gNNYZgRqGq0SQTwFCRLNEGeizRAuhIR
JRdeTnQ/F/CbX3R4NTDX01g3KpsA01+lSTHAoS/bvBW+6qE8liCPkzoMocAwHTze
bRyG47aIWjFBfBki4iIMpO9ccTSbczEfJuXRcEGf4WUVU0gER/I3rho8ZxApaSV9
xbkzhvBv7n7WlYsLAHq3EereOzEsKS39GUhzfdtGFElOplnoFLdeNQIDAQABo4IC
jjCCAoowHwYDVR0jBBgwFoAUyNl4aKLZGWjVPXLeXwo+3LWGhqYwHQYDVR0OBBYE
FGKsmv3MqNwaUJD6qF+p9k61Sv/HMA4GA1UdDwEB/wQEAwIFoDAMBgNVHRMBAf8E
AjAAMB0GA1UdJQQWMBQGCCsGAQUFBwMBBggrBgEFBQcDAjBJBgNVHSAEQjBAMDQG
CysGAQQBsjEBAgJOMCUwIwYIKwYBBQUHAgEWF2h0dHBzOi8vc2VjdGlnby5jb20v
Q1BTMAgGBmeBDAECATCBiAYIKwYBBQUHAQEEfDB6MEsGCCsGAQUFBzAChj9odHRw
Oi8vemVyb3NzbC5jcnQuc2VjdGlnby5jb20vWmVyb1NTTFJTQURvbWFpblNlY3Vy
ZVNpdGVDQS5jcnQwKwYIKwYBBQUHMAGGH2h0dHA6Ly96ZXJvc3NsLm9jc3Auc2Vj
dGlnby5jb20wggEEBgorBgEEAdZ5AgQCBIH1BIHyAPAAdgDd3Mo0ldfhFgXnlTL6
x5/4PRxQ39sAOhQSdgosrLvIKgAAAZfJQJeqAAAEAwBHMEUCIQDAgC2T6Yxl/A9Q
tWNWiDBCPKPpcMNYEO5jMCGs8D33iQIgebAaRjkGlaaAdErKsRmileB9+JP6krLK
ONDawiqec2kAdgAN4fIwK9MNwUBiEgnqVS78R3R8sdfpMO8OQh60fk6qNAAAAZfJ
QJdpAAAEAwBHMEUCIHaaxnSXfCatdHMBi3Xtl6po+Ic1HWlp3tUqlGHxq5a0AiEA
k8dq/ft72Sx1ibtwUMGvd4gv/NCKeA1c9i9Nmiw3qRIwLQYDVR0RBCYwJIIOcHJl
Y2lzb2dwcy5jb22CEnd3dy5wcmVjaXNvZ3BzLmNvbTANBgkqhkiG9w0BAQwFAAOC
AgEAJ6ITQ9vgygApembokSKAToi9mnWT6aPNqiCEM5gfmTRiZPNNKHW+cp3IbMRl
GPdGnjc7LOmFGM6/opdpsOQr6CbzoRVc0cm3kbhxJkS1YHnETS/tZqzyMF6L/VHr
oSbhPSW31WNVX/hFpzU3btZGqO9F7VwQdMs+aGRUpZ5M7ZlsNfVuoCMWFFulaYa1
OnTUSI3wukMN2fzALQonv0diinPtzObDoYYHGvgYzGlGYAnlkp6eWxCmkoBmrzHp
RODZEdcWxJHRaulqICPqfzaR23MoxtT8kzNuQ7qsd9jJx3RDiOK8HMoym8N+sH8Z
QI0ukjY0qnI5xv+01gFoTjeo15KEJGMh7pbgZKde7k410MacC3F/wosprRuFcrqw
3IYD82jZ17MLmkTKZVWyEMmJhsymWwQPODMXkIK9AwohfIkgCAb1EJJQAdQ+ZW1s
jTVS2zNv2UP6de3Hch0ZFmf3AYRIGnHlqZtq7TN7JEV1Qw71hWdjJn1EdDCPeKdG
PvTcuBlN4LnaL1S87ZfCoWncT3pJ4bJTLHuxFKia/GIpXBaf259srb/XOA/vWLSu
ghPHEq6ToiZ9qNMu/OAGXI9cLT2hdUq4R7nHSvUma9HXpo3WZp0L0BV9AOw1e/my
4xaTDc8Ceceu2kJwLGbXYlQB+6q+XmdxRbR/vGca6IOUFjM=
-----END CERTIFICATE-----`);
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
                const feed = (n) => ESC + 'd' + String.fromCharCode(n & 0xFF);
                const cut = GS + 'V' + '\x42' + '\x00';

                const NAME_W = 15;

                function lineItem(idx, name, dif, cargo) {
                    const nm = fitNameOneLine(name, NAME_W);
                    const dff = fmtDiff(dif).padStart(3, ' ');
                    const cStr = money(cargo).replace('$', '').padStart(8, ' ');
                    return `${String(idx).padStart(2,' ')} ${nm} ${dff} ${cStr}\n`;
                }

                // ===== NUEVO: sumar y detectar +70 =====
                let sumPos = 0,
                    sumNeg = 0,
                    hasPlus70 = false;
                (rows || []).forEach(r => {
                    const m = String(r?.dif ?? '').match(/[+\-]?\d+/);
                    if (!m) return;
                    const v = parseInt(m[0], 10);
                    if (isNaN(v)) return;
                    if (v > 0) {
                        sumPos += v;
                    } else if (v < 0) {
                        sumNeg += -v;
                    }
                    if (v >= 70) hasPlus70 = true; // ← aquí marcamos la alerta
                });
                // =======================================

                let out = init + fontB + normal;
                out += align(1) + boldOn + (empresa || 'EMPRESA') + '\n' + boldOff;
                out += 'SANCION DE MINUTOS CAIDOS\n';
                out += align(0);
                out += `Fecha: ${fecha || '--'}\n`;
                out += `Ruta : ${ruta  || '--'}\n`;
                out += `Placa: ${placa || '--'}\n`;
                out += hr();

                // Cuerpo
                out += SI;
                out += ` # ${'Geocerca'.padEnd(NAME_W,' ')} ${'Dif'.padStart(3,' ')} ${'Cargo'.padStart(8,' ')}\n`;
                out += hr();
                (rows || []).forEach(r => {
                    out += lineItem(r.idx, r.n || '', r.dif, r.cargo);
                });
                out += DC2;
                out += hr();

                // ===== NUEVO: mostrar Adelantos/Atrasos antes del TOTAL =====
                out += align(2) + `Adelantos: ${sumPos}\n`;
                out += align(2) + `Atrasos : ${sumNeg}\n`;
                // ============================================================

                out += align(2) + `Geocercas con caida: ${caidas || 0}\n`;
                out += boldOn + align(2) + `TOTAL: $${money(total)}\n` + boldOff;
                // ===== NUEVO: mensaje de alerta si hubo +70 =====
                if (hasPlus70) {
                    out += '\n' + align(1) + boldOn + 'ALERTA\n' + boldOff;
                    out += align(1) + 'Puede que la unidad haya\n';
                    out += align(1) + 'cerrado la vuelta antes de\n';
                    out += align(1) + 'iniciarla.\n';
                }
                // ================================================
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
        /* === Compactar aún más entre PLACA y RUTINA === */
        :root {
            --w-index: 28px;
            /* antes 30/36 */
            --w-placa: 92px;
            /* antes 128/140 */
            --w-rutina: 98px;
            /* antes 108/120 */
            --w-sancion: 85px;
            /* opcional bajar un poco */
            --w-plan: 55px;
            --w-eje: 50px;
            --w-dif: 35px;
            --w-mins: 54px;
            /* ancho columna Sanción al final */

            --header-accent: #005298;
            /* color encabezado */
            --sticky-body-gray: #EEE9E9;
            /* grisito columnas #/PLACA/RUTINA en body */
            --stripe-odd: #0a0a0b;
            /* zebra más visible (filas impares) */
            --header-accent: #005298;
            /* azul encabezados */
            --hover-row: #7db3f5;
            /* color de hover que tapa todo */
            --zebra-odd: #cfcfcf;
            /* <-- color para filas impares */
            --zebra-even: #ffffff;
            /* <-- opcional, pares */
        }

        /* 1) Encabezados de geocercas también en azul (fila superior del thead) */
        .table-compact thead tr:first-child th {
            background: var(--header-accent) !important;
            color: #fff !important;
            border-color: #00477f !important;
        }


        /* texto interno del título (span.stop-title) también blanco */
        .table-compact thead tr:first-child th .stop-title {
            color: #fff !important;
        }



        /* Asegura hover aún con .table-striped y celdas sticky */
        .table-compact.table-striped.row-hover>tbody>tr:nth-of-type(odd):hover>th,
        .table-compact.table-striped.row-hover>tbody>tr:nth-of-type(odd):hover>td,
        .table-compact.row-hover tbody tr:hover>td.sticky-col,
        .table-compact.row-hover tbody tr:hover>th.sticky-col {
            background: var(--hover-row) !important;
        }

        /* (opcional) cuando la fila tiene foco por teclado, mismo efecto que hover */
        .table-compact.row-hover tbody tr:focus-within>th,
        .table-compact.row-hover tbody tr:focus-within>td {
            background: var(--hover-row) !important;
        }

        /* Base: siempre gris para las 3 columnas fijas */
        /* Gris SIEMPRE en las 3 columnas fijas del body */
        .table-compact tbody td.sticky-index,
        .table-compact tbody td.sticky-placa,
        .table-compact tbody td.sticky-rutina,
        .table-compact tbody td.sticky-col {
            background: var(--sticky-body-gray) !important;
        }

        /* Evita que el zebra reemplace el gris en esas columnas */
        .table-compact.table-striped>tbody>tr:nth-of-type(odd)>td.sticky-index,
        .table-compact.table-striped>tbody>tr:nth-of-type(odd)>td.sticky-placa,
        .table-compact.table-striped>tbody>tr:nth-of-type(odd)>td.sticky-rutina,
        .table-compact.table-striped>tbody>tr:nth-of-type(odd)>td.sticky-col {
            background: var(--sticky-body-gray) !important;
        }

        /* Pares (por si quieres forzar blanco) */
        .table-compact.table-striped>tbody>tr:nth-of-type(even)>td:not(.sticky-col),
        .table-compact.table-striped>tbody>tr:nth-of-type(even)>th:not(.sticky-col) {
            background: var(--zebra-even) !important;
        }

        /* Impares */
        .table-compact.table-striped>tbody>tr:nth-of-type(odd)>td:not(.sticky-col),
        .table-compact.table-striped>tbody>tr:nth-of-type(odd)>th:not(.sticky-col) {
            background: var(--zebra-odd) !important;
        }

        /* (IMPORTANTE) Anula reglas previas que pintaban blanco o gris claro las sticky */
        .table-striped>tbody>tr:nth-of-type(odd) .sticky-col,
        .table-hover>tbody>tr:hover .sticky-col {
            background: var(--sticky-body-gray) !important;
        }

        /* Hover que se superpone a TODO (incluidas las 3 columnas fijas) */
        .table-compact.row-hover tbody tr:hover>* {
            background: var(--hover-row) !important;
            color: #111 !important;
        }

        /* En caso de zebra + hover, asegura que el hover gane en sticky también */
        .table-compact.table-striped.row-hover>tbody>tr:nth-of-type(odd):hover>td.sticky-col,
        .table-compact.row-hover>tbody>tr:hover>td.sticky-col {
            background: var(--hover-row) !important;
        }



        /* (Opcional) Mejor contraste de bordes en los headers de esas tres columnas */
        .table-compact thead th.col-index,
        .table-compact thead th.col-placa,
        .table-compact thead th.col-rutina {
            border-color: #00477f !important;
        }

        /* Nuevas columnas finales */
        .col-adelantos {
            min-width: var(--w-adelantos);
            width: var(--w-adelantos);
        }

        .col-atrasos {
            min-width: var(--w-atrasos);
            width: var(--w-atrasos);
        }

        /* Sanción bien a la derecha y sin espacio extra */
        .col-sancion {
            min-width: var(--w-sancion);
            width: var(--w-sancion);
            padding-right: .5rem !important;
        }

        .col-sancion a.sancion-link {
            display: inline-block;
            min-width: 0;
            text-decoration: underline;
        }

        /* Monospace para números de totales (opcional) */
        .col-adelantos,
        .col-atrasos {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
        }

        /* (si aún la ves muy a la izquierda, asegúrate de NO usar sticky en sanción) */
        .sticky-sancion {
            left: auto;
            box-shadow: none;
        }

        /* Nuevas columnas del final */
        .col-mins {
            min-width: var(--w-mins);
            width: var(--w-mins);
        }

        /* La sanción YA NO es sticky a la izquierda */
        .sticky-sancion {
            left: auto;
            box-shadow: none;
        }

        /* que se note que el monto es clickeable */
        a.sancion-amount {
            cursor: pointer;
            text-decoration: underline;
        }

        /* quitar “aire” a los lados de esas dos celdas fijas */
        .table-compact td.sticky-placa,
        .table-compact th.sticky-placa {
            padding-right: .10rem !important;
        }

        .table-compact td.sticky-rutina,
        .table-compact th.sticky-rutina {
            padding-left: .10rem !important;
        }

        /* texto del paréntesis más pequeño y apretado */
        .sticky-placa .small {
            font-size: .70rem;
            line-height: 1.0;
        }

        /* recalcular lefts (ya usan vars) y sombras sutiles */
        .sticky-index {
            left: 0;
        }

        .sticky-placa {
            left: var(--w-index);
        }

        .sticky-rutina {
            left: calc(var(--w-index) + var(--w-placa));
        }

        .sticky-sancion {
            left: calc(var(--w-index) + var(--w-placa) + var(--w-rutina));
        }

        /* encabezado más fino aún (opcional) */
        thead.table-light th {
            font-size: .64rem;
        }

        .table-compact {
            font-size: .80rem;
            /* más pequeño */
            line-height: 1.25;
        }

        .table-compact th,
        .table-compact td {
            padding: .22rem .30rem !important;
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
            color: black !important;
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
            color: #005298;
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

        /* ===== Layout más ancho y compacto ===== */
        .routes-toolbar {
            position: sticky;
            top: 64px;
            /* si tu navbar mide distinto, ajusta */
            z-index: 20;
            background: #fff;
            padding: .25rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, .06);
        }

        .route-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .route-scroll::-webkit-scrollbar-thumb {
            background: #d0d5dd;
            border-radius: 8px;
        }

        .route-btn {
            white-space: nowrap;
        }

        .route-btn.active {
            color: #fff !important;
        }

        /* Ocupa casi todo el alto de la ventana para scrollear dentro de la tabla */
        .tables-wrap {
            /* 100vh menos: header página (~56) + toolbar rutas (~44) + margenes */
            max-height: calc(100vh - 120px);
            overflow: hidden;
        }

        .table-wrap {
            height: 100%;
        }

        .table-scroller {
            height: calc(100% - 28px);
            /* deja visible el título de la ruta */
            overflow: auto;
            position: relative;
            isolation: isolate;
            /* ← clave para crear un stacking context propio */
            z-index: 0;

        }

        /* ===== Aún más compacto en la tabla ===== */


        .table-compact {
            font-size: .68rem;
            /* antes .74 */
            line-height: 1.02;
        }

        .table-compact th,
        .table-compact td {
            padding: .12rem .18rem !important;
            /* antes .18/.25 */
        }

        /* Todos los <th> del thead pegajosos y con z alto */
        .table-compact thead th {
            position: sticky;
            top: 0;
            z-index: 50;
            /* > que cualquier td */
            background: #fff;
            /* evita que “transparente” deje ver el body */
            box-shadow: 0 1px 0 rgba(0, 0, 0, .06);
            /* línea sutil bajo el header */
        }

        /* Las columnas fijas del thead aún más arriba que las fijas del body */
        .table-compact thead th.sticky-col {
            z-index: 60;
        }

        /* Asegura que las columnas fijas del body queden por debajo del header */
        .table-compact tbody .sticky-col {
            z-index: 20;
        }

        /* (opcional) segunda fila del thead también con la misma altura */
        .table-compact thead.table-light th {
            top: 0;
        }

        /* ya tenías sticky-top */
        /* títulos de paradas aún más compactos */
        .stop-title {
            max-width: 160px;
            -webkit-line-clamp: 2;
        }

        .sticky-index {
            left: 0;
            box-shadow: 2px 0 0 rgba(0, 0, 0, .03);
        }

        .sticky-placa {
            left: var(--w-index);
            box-shadow: 2px 0 0 rgba(0, 0, 0, .03);
        }

        .sticky-rutina {
            left: calc(var(--w-index) + var(--w-placa));
            box-shadow: 2px 0 0 rgba(0, 0, 0, .03);
        }

        .sticky-sancion {
            left: calc(var(--w-index) + var(--w-placa) + var(--w-rutina));
            box-shadow: 2px 0 0 rgba(0, 0, 0, .04);
        }

        /* Tipografía monospace y mejor alineación numérica */
        .table-compact .col-plan,
        .table-compact .col-eje,
        .table-compact .col-dif,
        .sancion-table .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
            letter-spacing: .1px;
        }

        /* Encabezado ultra fino */
        thead.table-light th {
            font-size: .66rem;
            font-weight: 700;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Botón "Ver" mini */


        /* Hover de fila completo (incluye sticky left y right) */
        .row-hover tbody tr:hover>* {
            background: #93bdf5 !important;
            /* color de realce */
        }

        .row-hover tbody tr:hover .sticky-col,
        .row-hover tbody tr:hover .sticky-right {
            background: #93bdf5 !important;
            /* asegura mismo color en columnas fijas */
        }

        /* También cuando una celda tiene foco (accesibilidad/teclado) */
        .row-hover tbody tr:focus-within>* {
            background: #93bdf5 !important;
        }

        /* Si usas .table-striped, esto evita que la raya se imponga sobre el hover */
        .table-striped>tbody>tr:nth-of-type(odd):hover>* {
            background: #93bdf5 !important;
        }

        /* 2) Hover que se impone sobre zebra y columnas sticky */
        .table-compact.row-hover tbody tr:hover>th,
        .table-compact.row-hover tbody tr:hover>td {
            background: var(--hover-row) !important;
            color: #111 !important;
        }

        /* === HOVER FULL-ROW QUE GANA A TODO === */

        /* 1) Anula zebra/colores previos en cualquier fila al pasar el mouse */
        .table-compact.row-hover tbody tr:hover>td,
        .table-compact.row-hover tbody tr:hover>th,
        .table-compact.row-hover.table-striped tbody tr:hover:nth-of-type(even)>td,
        .table-compact.row-hover.table-striped tbody tr:hover:nth-of-type(odd)>td,
        .table-compact.row-hover.table-striped tbody tr:hover:nth-of-type(even)>th,
        .table-compact.row-hover.table-striped tbody tr:hover:nth-of-type(odd)>th,
        .table-compact.row-hover tbody tr:hover>td.sticky-col,
        .table-compact.row-hover tbody tr:hover>th.sticky-col {
            background-color: var(--hover-row) !important;
            background-image: none !important;
            /* por si algún tema usa gradientes */
            color: #111 !important;
        }

        /* 2) Las columnas sticky NO quedan “fijas en blanco” cuando hay hover */
        .table-compact.row-hover tbody tr:hover .sticky-col {
            background-color: var(--hover-row) !important;
        }

        /* 3) (Saneamiento) si alguna celda quedó blanca por reglas anteriores, hereda del tr en hover */
        .table-compact.row-hover tbody tr:hover td,
        .table-compact.row-hover tbody tr:hover th {
            background: var(--hover-row) !important;
        }

        /* 4) Mantén el gris por defecto de las sticky SOLO cuando NO hay hover */
        .table-compact tbody .sticky-col {
            background-color: var(--sticky-body-gray) !important;
        }

        /* Misma apariencia azul para Plan/Eje/Dif en la 2ª fila del thead */
        .table-compact thead tr:nth-child(2) th.col-plan,
        .table-compact thead tr:nth-child(2) th.col-eje,
        .table-compact thead tr:nth-child(2) th.col-dif {
            background: var(--header-accent) !important;
            color: #fff !important;
            border-color: #00477f !important;
            text-align: center;
        }

        /* Centrar el reloj en el "medio-medio" del formulario */
        form.position-relative .clock-col {
            position: absolute;
            left: 50%;
            transform: translateX(-5%);
            bottom: .25rem;
            /* ajusta la altura para alinearlo con los controles */
            z-index: 5;
            /* por si hay overlaps */
            pointer-events: none;
            /* no bloquea clicks en inputs detrás */
        }

        /* Reloj un poco más grande */
        #relojEc {
            font-size: 1.35rem;
            /* antes ~1.05rem */
            padding: .55rem .9rem;
            /* un poco más de “aire” */
            border-radius: .5rem;
            letter-spacing: .5px;
            /* puedes subir a .6 si te gusta más “digital” */
        }

        /* En pantallas chicas, que el reloj vuelva a flujo normal y centrado abajo */
        @media (max-width: 767.98px) {
            form.position-relative .clock-col {
                position: static;
                transform: none;
                display: flex !important;
                justify-content: center;
                margin-top: .25rem;
                pointer-events: auto;
            }
        }


        .table-compact tbody td.sticky-rutina {
            font-weight: 400 !important;
            /* normal */
        }

        /* (por si algún contenido llegó con <b> o <strong>) */
        .table-compact tbody td.sticky-rutina b,
        .table-compact tbody td.sticky-rutina strong {
            font-weight: 400 !important;
        }

        /* Quitar negrilla de la celda RUTINA en el cuerpo de la tabla */
        .table-compact tbody td.sticky-placa {
            font-weight: 400 !important;
            /* normal */
        }

        /* 2) Aumenta padding en TODAS las celdas (gana a .table-sm) */
        .table.table-sm.table-compact th,
        .table.table-sm.table-compact td {
            padding: .36rem .34rem !important;
            /* ↑ alto | ancho */
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
    <script>
        (function() {
            const el = document.getElementById('relojEc');
            if (!el) return;

            function tick() {
                const str = new Intl.DateTimeFormat('es-EC', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false,
                    timeZone: 'America/Guayaquil'
                }).format(new Date());
                el.textContent = str;
            }

            tick(); // pinta al cargar
            setInterval(tick, 1000); // actualiza cada segundo
        })();
    </script>
    <script>
        (function() {
            const POLL_MS = 20000; // 20s

            // Aux: misma lógica de color que en PHP (difClass)
            function difClass(v) {
                if (v === null || v === '' || typeof v === 'undefined') return 'text-muted';
                const n = parseInt(v, 10);
                if (isNaN(n)) return 'text-secondary';
                if (n < 0) return 'text-danger';
                if (n > 0) return 'text-success';
                return 'text-secondary';
            }

            // Busca la tabla visible (ruta activa)
            function getActiveTableByRouteId(routeId) {
                const table = document.getElementById('tabla-' + routeId);
                return table || null;
            }

            // Recalcula y pinta una fila con nuevos plan/eje/dif
            function updateRow(tr, stops, tarifas, vuelta) {
                const plan = vuelta.horaProgramada || [];
                const eje = vuelta.horaEjecutada || [];
                const dif = vuelta.diferencia || [];

                const tds = tr.querySelectorAll('td');

                // Columna "RUTINA" está en el índice 2 (0=#,1=placa,2=rutina)
                const rutinaTd = tds[2];
                const first = plan?.[0] ?? '--:--';
                const last = plan?.[plan.length - 1] ?? '--:--';
                rutinaTd.textContent = `${first} - ${last}`;

                // Actualizamos columnas por parada (3 celdas por parada: Plan/Eje/Dif)
                let adelantos = 0,
                    atrasos = 0,
                    totalCargo = 0;

                for (let j = 0; j < stops.length; j++) {
                    const base = 3 + j * 3;
                    // Plan
                    tds[base + 0].textContent = plan[j] ?? '--:--';
                    // Eje
                    tds[base + 1].textContent = eje[j] ?? '--:--';
                    // Dif
                    const d = (dif[j] ?? null);
                    const tdDif = tds[base + 2];
                    tdDif.className = 'text-center col-dif ' + difClass(d);
                    tdDif.textContent = (d === null || d === '') ? '—' : ((parseInt(d, 10) > 0) ? ('+' + parseInt(d,
                        10)) : String(parseInt(d, 10)));

                    // Totales y sanción
                    if (d !== null && d !== '') {
                        const n = parseInt(d, 10);
                        if (!isNaN(n)) {
                            if (n > 0) adelantos += n;
                            else if (n < 0) atrasos += (-n);

                            if (n < 0) {
                                const stopId = (stops[j]?.id) ?? (stops[j]?.ID) ?? (stops[j]?.nid) ?? 0;
                                const tarifa = Number(tarifas[String(stopId)] || 0);
                                totalCargo += (-n) * tarifa;
                            }
                        }
                    }
                }

                // Pinta totales por fila
                const tdAdel = tr.querySelector('.col-adelantos');
                const tdAtra = tr.querySelector('.col-atrasos');
                if (tdAdel) tdAdel.textContent = `+${adelantos}`;
                if (tdAtra) tdAtra.textContent = `${atrasos}`;

                // Pinta la sanción calculada
                const aSancion = tr.querySelector('.col-sancion a.sancion-amount');
                if (aSancion) {
                    const total = (totalCargo || 0).toFixed(2);
                    aSancion.dataset.total = total;
                    aSancion.textContent = '$' + total;
                }
            }

            function parseDataAttrJSON(el, attr) {
                const raw = el.getAttribute(attr) || '{}';
                try {
                    return JSON.parse(raw);
                } catch {
                    return {};
                }
            }

            // Aplica un lote de datos (todas las rutas)
            function applyUpdate(payload) {
                if (!payload || !Array.isArray(payload.rutas)) return;

                for (const ruta of payload.rutas) {
                    const routeId = ruta.idRoute;
                    const table = getActiveTableByRouteId(routeId);
                    if (!table) continue;

                    const stops = parseDataAttrJSON(table, 'data-stops'); // array con { id / n / ... }
                    const tarifas = parseDataAttrJSON(table, 'data-tarifas'); // mapa "nimbusId" => valor

                    // Indexar TR por idUnidad (desde data-idunidad)
                    // Indexar TR por idUnidad (admite data-idunidad en <tr> o en un <td>)
                    const body = table.tBodies?.[0];
                    if (!body) continue;
                    const rowByUnidad = {};
                    body.querySelectorAll('tbody tr').forEach(tr => {
                        const holder = tr.matches('[data-idunidad]') ? tr : tr.querySelector('[data-idunidad]');
                        const id = holder ? String(holder.getAttribute('data-idunidad') || '') : '';
                        if (id) rowByUnidad[id] = tr;
                    });


                    // Actualizar cada vuelta que vino en el JSON
                    const vueltas = Array.isArray(ruta.data) ? ruta.data : [];
                    for (const v of vueltas) {
                        const key = String(v.idUnidad || '');
                        const tr = rowByUnidad[key];
                        if (!tr) continue; // si no encontramos la fila, la ignoramos
                        updateRow(tr, stops, tarifas, v);
                    }
                }
                // ...después de actualizar filas de esta ruta:
                scrollTableToBottom(table); // <- baja al final siempre

            }
            // === NUEVO: auto-scroll al final de la tabla ===
            function scrollTableToBottom(table) {
                if (!table) return;
                // el <table> está dentro de .table-responsive.table-scroller
                const scroller = table.parentElement?.classList?.contains('table-scroller') ?
                    table.parentElement :
                    table.closest('.table-scroller');
                if (scroller) scroller.scrollTop = scroller.scrollHeight;
            }

            // Hacer el fetch cada 20s
            async function pollOnce() {
                try {
                    const url = new URL(window.location.href);
                    url.searchParams.set('poll', '1'); // activa la rama JSON del controlador
                    const res = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    applyUpdate(data);
                } catch (e) {
                    // Silencioso para no molestar en UI; puedes loguear a consola si quieres
                    console.debug('poll error', e);
                }
            }

            // Primer intento y luego intervalos
            pollOnce();
            setInterval(pollOnce, POLL_MS);
            // primer auto-scroll al cargar
            const firstActiveTable = document.querySelector('.route-table:not(.d-none) table');
            scrollTableToBottom(firstActiveTable);
        })();


        // cuando cambias de ruta, baja al final de esa tabla
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.route-btn');
            if (!btn) return;
            setTimeout(() => {
                const pane = document.querySelector(btn.dataset.target);
                const table = pane?.querySelector('table');
                scrollTableToBottom(table);
            }, 0);
        });
    </script>

@endsection


@section('jsCode', 'js/scriptNavBar.js')
