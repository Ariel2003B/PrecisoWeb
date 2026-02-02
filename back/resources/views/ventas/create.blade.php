@extends('layout')

@section('Titulo', 'Nueva Venta')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Nueva Venta</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('ventas.index') }}">Ventas</a></li>
                        <li class="current">Nueva</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="container">
                <div class="card shadow-sm p-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="fw-bold mb-0">Crear Venta</h2>
                        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>

                    {{-- IVA vigente (solo para JS) --}}
                    <input type="hidden" id="iva-percent" value="{{ (float) $ivaPercent }}">

                    <form id="venta-form" method="POST" action="javascript:void(0);">
                        @csrf
                        <input type="hidden" name="DETALLE_JSON" id="hd-detalle-json">

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tipo de comprobante</label>
                                <select id="tipo-comprobante" name="TIPO_COMPROBANTE" class="form-select" required>
                                    <option value="FACTURA">Factura</option>
                                    <option value="NOTA_VENTA">Nota de Venta</option>
                                </select>
                                <small class="text-muted">
                                    * Si es Factura se calcula IVA ({{ (float) $ivaPercent }}% vigente).
                                </small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Descuento (%)</label>
                                <input type="number" class="form-control" id="descuento" name="PORCENTAJE_DESCUENTO"
                                    min="0" max="100" step="0.01" value="0">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Observación</label>
                                <input type="text" class="form-control" name="OBSERVACION" placeholder="Opcional">
                            </div>
                        </div>

                        <hr class="my-3">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6" id="empresa-wrap" style="display:none;">
                                <label class="form-label fw-bold">Empresa</label>
                                <select class="form-select" name="EMP_ID" id="empresa-select">
                                    <option value="">Selecciona empresa...</option>
                                    @foreach ($empresas as $e)
                                        <option value="{{ $e->EMP_ID }}">{{ $e->NOMBRE }} - RUC:
                                            {{ $e->RUC }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" id="cliente-wrap">
                                <label class="form-label fw-bold">Cliente</label>
                                <select class="form-select" name="USU_ID_CLIENTE" id="cliente-select" required>
                                    <option value="">Selecciona cliente...</option>
                                    @foreach ($usuarios as $u)
                                        <option value="{{ $u->USU_ID }}">
                                            {{ $u->APELLIDO }} {{ $u->NOMBRE }} - {{ $u->CEDULA ?? $u->CORREO }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label fw-bold">Vendedor</label>
                                <select class="form-select" name="USU_ID_VENDEDOR" id="vendedor-select" required>
                                    <option value="">Selecciona vendedor...</option>
                                    @foreach ($usuarios as $u)
                                        <option value="{{ $u->USU_ID }}">
                                            {{ $u->APELLIDO }} {{ $u->NOMBRE }} - {{ $u->CORREO }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="chk-empresa">
                                    <label class="form-check-label fw-bold" for="chk-empresa">
                                        Facturar a empresa
                                    </label>
                                </div>
                            </div>


                        </div>

                        <hr class="my-3">

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Producto</label>
                                <select id="producto-tipo" class="form-select">
                                    <option value="EQUIPO">Equipos / Accesorios</option>
                                    <option value="SIMCARD">SIMCARD</option>
                                </select>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3">Detalle: Equipos / Accesorios</h5>
                        <div id="bloque-equipos">

                            <div class="row g-2 align-items-end mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Equipo / Accesorio</label>
                                    <select id="equipo-select" class="form-select">
                                        <option value="">Selecciona...</option>
                                        @foreach ($equipos as $e)
                                            <option value="{{ $e->EQU_ID }}" data-nombre="{{ $e->EQU_NOMBRE }}"
                                                data-precio="{{ (float) $e->EQU_PRECIO }}"
                                                data-stock="{{ (int) $e->EQU_STOCK }}">
                                                {{ $e->EQU_NOMBRE }} (Stock: {{ $e->EQU_STOCK }}) -
                                                ${{ number_format((float) $e->EQU_PRECIO, 2, '.', ',') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Cantidad</label>
                                    <input type="number" id="equipo-cantidad" class="form-control" min="1"
                                        value="1">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Precio</label>
                                    <input type="text" id="equipo-precio" class="form-control" readonly>
                                </div>

                                <div class="col-md-2 d-grid">
                                    <button type="button" id="btn-agregar" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="bloque-simcard" style="display:none;">
                            <h5 class="fw-bold mb-3">Detalle: SIMCARD</h5>

                            <div class="row g-2 align-items-end mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Número</label>
                                    <input type="text" id="sim-search" class="form-control"
                                        placeholder="Buscar número...">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">SIMCARD</label>
                                    <select id="sim-select" class="form-select">
                                        <option value="">Selecciona...</option>
                                    </select>
                                </div>

                                <div class="col-md-2 d-grid">
                                    <button type="button" id="btn-ver-contratos" class="btn btn-primary" disabled>
                                        <i class="bi bi-card-checklist"></i> Contratos
                                    </button>
                                </div>
                            </div>


                            <div class="alert alert-warning" id="sim-no-contratos" style="display:none;">
                                Esta SIM no tiene contratos disponibles para facturar (todo está facturado).
                            </div>

                            <div class="row g-3" id="sim-contratos" style="display:none;">
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-bold mb-2">Hardware (contratos)</div>
                                        <div id="list-hardware" class="d-grid gap-2"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-bold mb-2">Servicio (contratos)</div>
                                        <div id="list-servicio" class="d-grid gap-2"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden para enviar luego al store --}}
                            <input type="hidden" name="SIM_ID" id="hd-sim-id">
                            <input type="hidden" name="SIM_CONTRATO_TIPO" id="hd-sim-contrato-tipo">
                            <input type="hidden" name="SIM_CONTRATO_ID" id="hd-sim-contrato-id">
                            <input type="hidden" name="SIM_PRECIO" id="hd-sim-precio">
                        </div>


                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="detalle-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-end">Subtotal</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="row-empty">
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Agrega equipos para armar la venta.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Totales --}}
                        <div class="row justify-content-end mt-3">
                            <div class="col-md-5">
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold">Subtotal</span>
                                        <span id="txt-subtotal">$ 0.00</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold">Descuento</span>
                                        <span id="txt-descuento">$ 0.00</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold">IVA</span>
                                        <span id="txt-iva">$ 0.00</span>
                                    </div>

                                    <hr class="my-2">

                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold fs-5">Total</span>
                                        <span class="fw-bold fs-5" id="txt-total">$ 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden para enviar luego al store --}}
                        <input type="hidden" name="SUBTOTAL" id="hd-subtotal" value="0">
                        <input type="hidden" name="IVA" id="hd-iva" value="0">
                        <input type="hidden" name="TOTAL" id="hd-total" value="0">

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Cancelar</a>

                            <button type="submit" class="btn btn-primary" id="btn-guardar" disabled>
                                Guardar
                            </button>

                        </div>
                    </form>

                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="modalContratos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        Contratos de SIM: <span id="modal-sim-numero"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning" id="modal-no-contratos" style="display:none;">
                        Esta SIM no tiene contratos disponibles para facturar.
                    </div>

                    <div class="row g-3" id="modal-contratos-wrap">
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="fw-bold mb-2">Hardware</div>
                                <div id="modal-list-hardware" class="d-grid gap-2"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="fw-bold mb-2">Servicio</div>
                                <div id="modal-list-servicio" class="d-grid gap-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-agregar-contratos">
                        <i class="bi bi-plus-circle"></i> Agregar seleccionados
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function round2(n) {
            return Math.round((n + Number.EPSILON) * 100) / 100;
        }

        function money(n) {
            const v = round2(n);
            return '$ ' + v.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function recalc(items, ivaPercent, tipoComprobante, descuentoInput, refs) {
            // Total bruto: suma de precios tal como están guardados (con IVA incluido)
            const totalConIva = items.reduce((acc, it) => acc + (it.precio * (it.tipo === 'SIM_CONTRATO' ? 1 : it
                .cantidad)), 0);

            // Convertir a base sin IVA (si ivaPercent es 0, factor = 1)
            const factor = 1 + (ivaPercent / 100);
            const subtotalSinIva = (ivaPercent > 0) ? (totalConIva / factor) : totalConIva;
            const ivaValor = totalConIva - subtotalSinIva;

            // Descuento aplicado sobre la base (sin IVA)
            const descPct = Math.max(0, Math.min(100, parseFloat(descuentoInput.value || '0')));
            const descuento = subtotalSinIva * (descPct / 100);

            const baseConDescuento = subtotalSinIva - descuento;

            // Si es FACTURA: total = baseConDescuento + IVA proporcional
            // Si es NOTA_VENTA: total = baseConDescuento y no se muestra IVA
            let ivaFinal = 0;
            let totalFinal = 0;

            if (tipoComprobante.value === 'FACTURA') {
                // IVA proporcional en base con descuento
                ivaFinal = baseConDescuento * (ivaPercent / 100);
                totalFinal = baseConDescuento + ivaFinal;
            } else {
                // NOTA DE VENTA: total baja al quitar IVA
                ivaFinal = 0;
                totalFinal = baseConDescuento;
            }

            // pintar
            refs.txtSubtotal.textContent = money(baseConDescuento); // base imponible
            refs.txtDescuento.textContent = money(descuento);
            refs.txtIva.textContent = money(ivaFinal);
            refs.txtTotal.textContent = money(totalFinal);

            // hidden
            refs.hdSubtotal.value = round2(baseConDescuento);
            refs.hdIva.value = round2(ivaFinal);
            refs.hdTotal.value = round2(totalFinal);
        }
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chkEmpresa = document.getElementById('chk-empresa');
            const empresaWrap = document.getElementById('empresa-wrap');
            const empresaSelect = document.getElementById('empresa-select');

            const clienteWrap = document.getElementById('cliente-wrap');
            const clienteSelect = document.getElementById('cliente-select');

            function netOfVat(gross, ivaPercent) {
                const factor = 1 + (ivaPercent / 100);
                return (ivaPercent > 0) ? (gross / factor) : gross;
            }


            function toggleFacturacionEmpresa() {
                const isEmpresa = chkEmpresa.checked;

                // Mostrar/Ocultar
                empresaWrap.style.display = isEmpresa ? '' : 'none';
                clienteWrap.style.display = isEmpresa ? 'none' : '';

                if (isEmpresa) {
                    // No enviar cliente
                    clienteSelect.value = '';
                    clienteSelect.required = false;
                    clienteSelect.disabled = true;

                    // Enviar empresa
                    empresaSelect.required = true;
                    empresaSelect.disabled = false;
                } else {
                    // No enviar empresa
                    empresaSelect.value = '';
                    empresaSelect.required = false;
                    empresaSelect.disabled = true;

                    // Enviar cliente
                    clienteSelect.required = true;
                    clienteSelect.disabled = false;
                }
            }

            chkEmpresa.addEventListener('change', toggleFacturacionEmpresa);

            // inicial
            empresaSelect.disabled = true;
            toggleFacturacionEmpresa();


            // ====== Toggle producto ======
            const productoTipo = document.getElementById('producto-tipo');
            const bloqueEquipos = document.getElementById('bloque-equipos');
            const bloqueSim = document.getElementById('bloque-simcard');

            productoTipo.addEventListener('change', () => {
                const isSim = productoTipo.value === 'SIMCARD';
                bloqueEquipos.style.display = isSim ? 'none' : '';
                bloqueSim.style.display = isSim ? '' : 'none';
            });

            // ====== Carrito (items) + render ======
            const tipoComprobante = document.getElementById('tipo-comprobante');
            const descuentoInput = document.getElementById('descuento');
            const ivaPercent = parseFloat(document.getElementById('iva-percent').value || '0');

            const tbody = document.querySelector('#detalle-table tbody');
            const rowEmpty = document.getElementById('row-empty');

            const txtSubtotal = document.getElementById('txt-subtotal');
            const txtDescuento = document.getElementById('txt-descuento');
            const txtIva = document.getElementById('txt-iva');
            const txtTotal = document.getElementById('txt-total');

            const hdSubtotal = document.getElementById('hd-subtotal');
            const hdIva = document.getElementById('hd-iva');
            const hdTotal = document.getElementById('hd-total');

            const items = [];

            function round2(n) {
                return Math.round((n + Number.EPSILON) * 100) / 100;
            }

            function money(n) {
                const v = round2(n);
                return '$ ' + v.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function recalcUI() {
                const baseSinIva = items.reduce((acc, it) => {
                    const qty = (it.tipo === 'SIM_CONTRATO') ? 1 : (it.cantidad || 1);
                    return acc + (netOfVat(it.precio, ivaPercent) * qty);
                }, 0);

                const descPct = Math.max(0, Math.min(100, parseFloat(descuentoInput.value || '0')));
                const descuento = baseSinIva * (descPct / 100);
                const baseConDescuento = baseSinIva - descuento;

                let ivaFinal = 0;
                let totalFinal = 0;

                if (tipoComprobante.value === 'FACTURA') {
                    ivaFinal = baseConDescuento * (ivaPercent / 100);
                    totalFinal = baseConDescuento + ivaFinal;
                } else {
                    ivaFinal = 0;
                    totalFinal = baseConDescuento;
                }

                // pintar
                txtSubtotal.textContent = money(baseConDescuento);
                txtDescuento.textContent = money(descuento);
                txtIva.textContent = money(ivaFinal);
                txtTotal.textContent = money(totalFinal);

                // hidden
                hdSubtotal.value = round2(baseConDescuento);
                hdIva.value = round2(ivaFinal);
                hdTotal.value = round2(totalFinal);

            }

            function render() {
                tbody.querySelectorAll('tr').forEach(tr => {
                    if (tr !== rowEmpty) tr.remove();
                });

                rowEmpty.style.display = (items.length === 0) ? '' : 'none';

                items.forEach((it, idx) => {
                    const tr = document.createElement('tr');
                    const isContrato = it.tipo === 'SIM_CONTRATO';
                    const qty = isContrato ? 1 : it.cantidad;
                    const subtotalFilaSinIva = netOfVat(it.precio, ivaPercent) * qty;

                    tr.innerHTML = `
        <td>
          <div class="fw-semibold">${it.nombre}</div>
          ${
            isContrato
            ? `<div class="text-muted" style="font-size:12px;">SIM: ${it.sim_id} • ${it.contrato_tipo} #${it.contrato_id}</div>`
            : `<div class="text-muted" style="font-size:12px;">ID: ${it.equ_id} • Stock: ${it.stock}</div>`
          }
        </td>

        <td class="text-center">
          ${
            isContrato
              ? `<span class="text-muted">—</span>`
              : `<input type="number" min="1" class="form-control form-control-sm text-center"
                                                                           style="max-width:90px; margin:0 auto;" value="${it.cantidad}" data-idx="${idx}">`
          }
        </td>

        <td class="text-end">${money(it.precio)}</td>
        
        <td class="text-end fw-bold">${money(subtotalFilaSinIva)}</td>


        <td class="text-center">
          <button type="button" class="btn btn-sm btn-outline-danger" data-del="${idx}">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      `;

                    tbody.appendChild(tr);
                });

                recalcUI();
            }

            // actualizar cantidades (solo equipos)
            tbody.addEventListener('input', (e) => {
                const inp = e.target;
                if (inp.matches('input[data-idx]')) {
                    const idx = parseInt(inp.dataset.idx);
                    let val = parseInt(inp.value || '1');
                    if (val < 1) val = 1;

                    if (items[idx].stock != null && val > items[idx].stock) {
                        alert(`Stock insuficiente. Disponible: ${items[idx].stock}`);
                        val = items[idx].stock;
                    }
                    items[idx].cantidad = val;
                    render();
                }
            });

            tbody.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-del]');
                if (!btn) return;
                const idx = parseInt(btn.dataset.del);
                items.splice(idx, 1);
                render();
            });

            tipoComprobante.addEventListener('change', recalcUI);
            descuentoInput.addEventListener('input', recalcUI);

            // ====== Equipos: agregar ======
            const equipoSelect = document.getElementById('equipo-select');
            const cantidadInput = document.getElementById('equipo-cantidad');
            const precioInput = document.getElementById('equipo-precio');
            const btnAgregar = document.getElementById('btn-agregar');

            equipoSelect.addEventListener('change', () => {
                const opt = equipoSelect.options[equipoSelect.selectedIndex];
                const precio = opt?.dataset?.precio ? parseFloat(opt.dataset.precio) : 0;
                precioInput.value = precio ? precio.toFixed(2) : '';
                cantidadInput.value = 1;
            });

            btnAgregar.addEventListener('click', () => {
                const opt = equipoSelect.options[equipoSelect.selectedIndex];
                const equId = parseInt(equipoSelect.value || '0');
                if (!equId) return;

                const nombre = opt.dataset.nombre;
                const precio = parseFloat(opt.dataset.precio || '0');
                const stock = parseInt(opt.dataset.stock || '0');
                const cant = parseInt(cantidadInput.value || '1');

                if (cant <= 0) return;

                const existing = items.find(x => x.equ_id === equId && !x.tipo);
                if (existing) {
                    const nuevaCant = existing.cantidad + cant;
                    if (nuevaCant > stock) {
                        alert(`Stock insuficiente. Disponible: ${stock}`);
                        return;
                    }
                    existing.cantidad = nuevaCant;
                } else {
                    if (cant > stock) {
                        alert(`Stock insuficiente. Disponible: ${stock}`);
                        return;
                    }
                    items.push({
                        equ_id: equId,
                        nombre,
                        precio,
                        cantidad: cant,
                        stock
                    });
                }

                equipoSelect.value = '';
                precioInput.value = '';
                cantidadInput.value = 1;
                render();
            });

            // ====== SIMCARDS + MODAL ======
            const simSearch = document.getElementById('sim-search');
            const simSelect = document.getElementById('sim-select');
            const btnVerContratos = document.getElementById('btn-ver-contratos');

            const modalEl = document.getElementById('modalContratos');
            const modal = new bootstrap.Modal(modalEl);

            const modalSimNumero = document.getElementById('modal-sim-numero');
            const modalNoContratos = document.getElementById('modal-no-contratos');
            const modalWrap = document.getElementById('modal-contratos-wrap');
            const modalListHw = document.getElementById('modal-list-hardware');
            const modalListSv = document.getElementById('modal-list-servicio');
            const btnAgregarContratos = document.getElementById('btn-agregar-contratos');

            let contratosCache = {
                sim: null,
                hardware: [],
                servicio: []
            };
            let timerSim = null;

            async function fetchSimcards(q = '') {
                const url = new URL("{{ route('ventas.simcards.disponibles') }}", window.location.origin);
                if (q) url.searchParams.set('q', q);
                const res = await fetch(url);
                const data = await res.json();

                simSelect.innerHTML = `<option value="">Selecciona...</option>`;
                data.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.ID_SIM;
                    opt.textContent = s.NUMEROTELEFONO;
                    simSelect.appendChild(opt);
                });

                btnVerContratos.disabled = true;
            }

            async function fetchContratos(simId) {
                const url = "{{ url('/ventas/simcards') }}/" + simId + "/contratos";
                const res = await fetch(url);
                const data = await res.json();
                contratosCache = data;
                return data;
            }

            function renderModal(data) {
                modalListHw.innerHTML = '';
                modalListSv.innerHTML = '';
                modalSimNumero.textContent = data?.sim?.NUMEROTELEFONO ?? '';

                const hw = data.hardware || [];
                const sv = data.servicio || [];

                if (hw.length === 0 && sv.length === 0) {
                    modalNoContratos.style.display = '';
                    modalWrap.style.display = 'none';
                    return;
                }
                modalNoContratos.style.display = 'none';
                modalWrap.style.display = '';

                // HARDWARE (VALOR_TOTAL)
                hw.forEach(c => {
                    const precio = parseFloat(c.valor_total || 0);
                    const id = `hw-${c.id}`;
                    const div = document.createElement('div');
                    div.className = 'form-check border rounded p-2';
                    div.innerHTML = `
        <input class="form-check-input" type="checkbox" value="${c.id}" id="${id}"
               data-tipo="HARDWARE" data-precio="${precio}">
        <label class="form-check-label w-100" for="${id}">
          <div class="fw-bold">Contrato HARDWARE #${c.id}</div>
          <div class="text-muted" style="font-size:12px;">
            Activación: ${c.fecha ?? '-'} • Sig. pago: ${c.siguiente_pago ?? '-'} • Total: $${precio.toFixed(2)}
          </div>
        </label>
      `;
                    modalListHw.appendChild(div);
                });

                // SERVICIO (VALOR_PAGO)
                sv.forEach(c => {
                    const precio = parseFloat(c.valor_pago || 0);
                    const id = `sv-${c.id}`;
                    const div = document.createElement('div');
                    div.className = 'form-check border rounded p-2';
                    div.innerHTML = `
        <input class="form-check-input" type="checkbox" value="${c.id}" id="${id}"
               data-tipo="SERVICIO" data-precio="${precio}">
        <label class="form-check-label w-100" for="${id}">
          <div class="fw-bold">Contrato SERVICIO #${c.id}</div>
          <div class="text-muted" style="font-size:12px;">
            Fecha: ${c.fecha ?? '-'} • Sig. pago: ${c.siguiente_pago ?? '-'} • Valor: $${precio.toFixed(2)}
          </div>
        </label>
      `;
                    modalListSv.appendChild(div);
                });
            }

            function addContratoToCart({
                sim_id,
                sim_numero,
                contrato_tipo,
                contrato_id,
                precio
            }) {
                const exists = items.some(x =>
                    x.tipo === 'SIM_CONTRATO' &&
                    x.sim_id === sim_id &&
                    x.contrato_tipo === contrato_tipo &&
                    x.contrato_id === contrato_id
                );
                if (exists) return;

                items.push({
                    tipo: 'SIM_CONTRATO',
                    sim_id,
                    contrato_tipo,
                    contrato_id,
                    nombre: `${sim_numero} • ${contrato_tipo} #${contrato_id}`,
                    precio: parseFloat(precio || 0),
                    cantidad: 1,
                    stock: null
                });
            }

            simSearch.addEventListener('input', () => {
                clearTimeout(timerSim);
                timerSim = setTimeout(() => fetchSimcards(simSearch.value.trim()), 250);
            });

            simSelect.addEventListener('change', () => {
                btnVerContratos.disabled = !simSelect.value;
            });

            btnVerContratos.addEventListener('click', async () => {
                const simId = simSelect.value;
                if (!simId) return;
                const data = await fetchContratos(simId);
                renderModal(data);
                modal.show();
            });

            btnAgregarContratos.addEventListener('click', () => {
                const simId = parseInt(simSelect.value || '0');
                const simNumero = contratosCache?.sim?.NUMEROTELEFONO || '';

                const checks = modalEl.querySelectorAll('input.form-check-input:checked');
                if (checks.length === 0) {
                    alert('Selecciona al menos un contrato.');
                    return;
                }

                checks.forEach(chk => {
                    addContratoToCart({
                        sim_id: simId,
                        sim_numero: simNumero,
                        contrato_tipo: chk.dataset.tipo,
                        contrato_id: parseInt(chk.value),
                        precio: parseFloat(chk.dataset.precio || '0')
                    });
                });

                render();
                modal.hide();
            });

            // init
            fetchSimcards();
            render();

            const form = document.getElementById('venta-form');
            const hdDetalleJson = document.getElementById('hd-detalle-json');
            const btnGuardar = document.getElementById('btn-guardar');

            function refreshGuardar() {
                // habilitar si hay items y vendedor y (cliente o empresa)
                const vendedorOk = !!document.getElementById('vendedor-select').value;
                const isEmpresa = chkEmpresa.checked;
                const clienteOk = isEmpresa ? true : !!clienteSelect.value;
                const empresaOk = isEmpresa ? !!empresaSelect.value : true;

                btnGuardar.disabled = !(items.length > 0 && vendedorOk && clienteOk && empresaOk);
            }

            function syncDetalleJson() {
                const ivaPct = ivaPercent; // el de arriba (parseFloat del hidden)
                const detalle = items.map(it => {
                    const isContrato = it.tipo === 'SIM_CONTRATO';
                    const qty = isContrato ? 1 : (parseInt(it.cantidad || 1));

                    const precioBruto = parseFloat(it.precio || 0); // con IVA
                    const precioSinIva = netOfVat(precioBruto, ivaPct); // sin IVA
                    const subtotalSinIva = precioSinIva * qty;

                    return {
                        // tipo
                        tipo: isContrato ? 'SIM_CONTRATO' : 'EQUIPO',

                        // ids
                        equ_id: it.equ_id ?? null,
                        sim_id: it.sim_id ?? null,
                        contrato_tipo: it.contrato_tipo ?? null,
                        contrato_id: it.contrato_id ?? null,

                        // cantidades
                        cantidad: qty,

                        // IMPORTANTES (sin IVA)
                        precio: round2(precioSinIva),
                        subtotal: round2(subtotalSinIva),

                        // solo informativo si quieres auditar
                        precio_bruto: round2(precioBruto),
                    };
                });

                hdDetalleJson.value = JSON.stringify(detalle);
            }


            form.addEventListener('submit', (e) => {
                e.preventDefault();
                syncDetalleJson();

                // ahora sí mandar normal
                form.action = "{{ route('ventas.store') }}";
                form.method = "POST";
                form.submit();
            });

            // cada vez que cambia algo importante
            document.getElementById('vendedor-select').addEventListener('change', refreshGuardar);
            clienteSelect.addEventListener('change', refreshGuardar);
            empresaSelect.addEventListener('change', refreshGuardar);
            chkEmpresa.addEventListener('change', refreshGuardar);

            // también cuando renderizas
            const oldRender = render;
            render = function() {
                oldRender();
                refreshGuardar();
            };
            refreshGuardar();



        });
    </script>



@endsection
