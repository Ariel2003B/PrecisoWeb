@extends('layout')

@section('Titulo', 'Editar SIM Card')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Editar SIM Card</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('simcards.index') }}">Simcards</a></li>
                        <li class="current">Editar SIM Card</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="container">
                <!-- Page Title -->
                <form action="{{ route('simcards.update', $simcard->ID_SIM) }}" id="editSimCardForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="deps_migrated" id="deps_migrated" value="0">

                    <div class="mb-3">
                        <label for="PROPIETARIO" class="form-label">PROPIETARIO</label>
                        <select name="PROPIETARIO" id="PROPIETARIO" class="form-control">
                            <option value="PRECISOGPS S.A.S."
                                {{ $simcard->PROPIETARIO === 'PRECISOGPS S.A.S.' ? 'selected' : '' }}>
                                PRECISOGPS S.A.S.
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="PROVEEDOR" class="form-label">PROVEEDOR</label>
                        <input type="text" name="PROVEEDOR" id="PROVEEDOR" class="form-control"
                            value="{{ old('PROVEEDOR', $simcard->PROVEEDOR) }}" placeholder="Ingrese el nombre del proveedor"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="CUENTA" class="form-label">Cuenta / Código principal</label>
                        <input type="text" name="CUENTA" id="CUENTA" class="form-control"
                            value="{{ old('CUENTA', $simcard->CUENTA) }}" placeholder="Ingrese la cuenta de la SIM"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="NUMEROTELEFONO" class="form-label">Número de Teléfono / Login</label>
                        <input type="text" name="NUMEROTELEFONO" id="NUMEROTELEFONO" class="form-control" maxlength="50"
                            value="{{ old('NUMEROTELEFONO', $simcard->NUMEROTELEFONO) }}"
                            placeholder="Ingrese el número de teléfono" required>
                    </div>
                    <div class="mb-3">
                        <label for="PLAN" class="form-label">Plan / Descripción</label>
                        <input type="text" name="PLAN" id="PLAN" class="form-control" maxlength="255"
                            value="{{ old('PLAN', $simcard->PLAN) }}" placeholder="Ingrese el plan">
                    </div>
                    <div class="mb-3">
                        <label for="TIPOPLAN" class="form-label">Codigo de Plan / Dirección</label>
                        <input type="text" name="TIPOPLAN" id="TIPOPLAN" class="form-control" maxlength="255"
                            value="{{ old('TIPOPLAN', $simcard->TIPOPLAN) }}" placeholder="Ingrese el codigo de plan"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="ICC" class="form-label">ICC / Contrato</label>
                        <input type="text" name="ICC" id="ICC" class="form-control"
                            value="{{ old('ICC', $simcard->ICC) }}" placeholder="Ingrese el ICC">
                    </div>
                    <div class="mb-3">
                        <label for="PLATAFORMA" class="form-label">PLATAFORMA</label>
                        <input type="text" name="PLATAFORMA" id="PLATAFORMA" class="form-control"
                            value="{{ old('PLATAFORMA', $simcard->PLATAFORMA) }}" placeholder="Ingrese la plataforma">
                    </div>

                    <div class="mb-3">
                        <label for="ASIGNACION" class="form-label">Asignacion</label>
                        <input type="text" name="ASIGNACION" id="ASIGNACION" class="form-control"
                            value="{{ old('ASIGNACION', $simcard->ASIGNACION) }}" placeholder="Ingrese la Asignacion">
                    </div>
                    <div class="mb-3">
                        <label for="IMEI" class="form-label">Imei</label>
                        <input type="text" name="IMEI" id="IMEI" class="form-control"
                            value="{{ old('IMEI', $simcard->IMEI) }}" placeholder="Ingrese el IMEI del equipo">
                    </div>
                    <div class="mb-3">
                        <label for="EQUIPO" class="form-label">EQUIPO</label>
                        <select name="EQUIPO" id="EQUIPO" class="form-control">
                            <option value="">Sin asignar</option>
                            <option value="GPS" {{ $simcard->EQUIPO === 'GPS' ? 'selected' : '' }}>GPS</option>
                            <option value="MODEM" {{ $simcard->EQUIPO === 'MODEM' ? 'selected' : '' }}>MODEM</option>
                            <option value="MOVIL" {{ $simcard->EQUIPO === 'MOVIL' ? 'selected' : '' }}>MOVIL</option>
                            <option value="COMPUTADOR ABORDO"
                                {{ $simcard->EQUIPO === 'COMPUTADOR ABORDO' ? 'selected' : '' }}>
                                COMPUTADOR ABORDO</option>
                            <option value="LECTOR DE QR" {{ $simcard->EQUIPO === 'LECTOR DE QR' ? 'selected' : '' }}>
                                LECTOR DE QR
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="MARCA_EQUIPO" class="form-label">Marca del Equipo</label>
                        <input type="text" name="MARCA_EQUIPO" id="MARCA_EQUIPO" class="form-control"
                            value="{{ old('MARCA_EQUIPO', $simcard->MARCA_EQUIPO) }}"
                            placeholder="Ingrese la marca del equipo">
                    </div>
                    <div class="mb-3">
                        <label for="MODELO_EQUIPO" class="form-label">Modelo del Equipo</label>
                        <input type="text" name="MODELO_EQUIPO" id="MODELO_EQUIPO" class="form-control"
                            value="{{ old('MODELO_EQUIPO', $simcard->MODELO_EQUIPO) }}"
                            placeholder="Ingrese el modelo del equipo">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <div>
                            <input type="radio" id="estado_activa" name="ESTADO" value="ACTIVA"
                                {{ $simcard->ESTADO === 'ACTIVA' ? 'checked' : '' }}>
                            <label for="estado_activa">Activa</label>
                            <input type="radio" id="estado_libre" name="ESTADO" value="LIBRE"
                                {{ $simcard->ESTADO === 'LIBRE' ? 'checked' : '' }}>
                            <label for="estado_libre">Libre</label>
                            <input type="radio" id="estado_eliminada" name="ESTADO" value="ELIMINADA"
                                {{ $simcard->ESTADO === 'ELIMINADA' ? 'checked' : '' }}>
                            <label for="estado_eliminada">Eliminada</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    <a href="{{ route('simcards.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </section>
    </main>
    <div class="modal fade" id="modalMigrarDependencias" tabindex="-1" aria-labelledby="modalMigrarDependenciasLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="modalMigrarDependenciasLabel">Migrar dependencias a otra SIM</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Esta SIM tiene contratos y/o documentos asociados. Debes migrarlos a otra SIM <b>sin
                            detalles asignados</b> antes de cambiar su estado a <b>LIBRE</b> o <b>ELIMINADA</b>.</p>
                    <div id="depsResumen" class="small text-muted mb-3"></div>

                    <div class="mb-3">
                        <label class="form-label">Selecciona SIM destino</label>
                        <select id="target_sim_id" class="form-select"></select>
                        <div class="form-text">Solo se listan SIMs sin detalles asignados.</div>
                    </div>
                    <div id="migrarAlert" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnConfirmarMigracion" class="btn btn-primary">
                        Migrar dependencias
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editSimCardForm');
            const estadoInicial = "{{ $simcard->ESTADO }}";
            const equipoSelect = document.getElementById('EQUIPO');

            // Bloquear automáticamente campos al cargar según el estado inicial
            aplicarEstadoInicial(estadoInicial);

            // Detectar cambios en los radio buttons
            form.addEventListener('change', function(e) {
                if (e.target.name === 'ESTADO') {
                    handleEstadoChange(e.target.value);
                }
            });

            // Validar antes de enviar el formulario
            form.addEventListener('submit', function(e) {
                const estado = document.querySelector('input[name="ESTADO"]:checked').value;
                if (estado === 'ACTIVA') {
                    const camposRequeridos = ['PROPIETARIO', 'CUENTA', 'NUMEROTELEFONO', 'TIPOPLAN', 'PLAN',
                        'ICC', 'EQUIPO', 'IMEI'
                    ];
                    const incompletos = camposRequeridos.some(campo => {
                        const input = document.getElementById(campo);
                        return !input || !input.value.trim();
                    });

                    if (incompletos) {
                        e.preventDefault();
                        alert(
                            "Todos los campos requeridos deben estar completos para establecer el estado como ACTIVA."
                        );
                    }
                }
                if (estado === 'LIBRE') {
                    const camposRequeridos = ['PROPIETARIO', 'CUENTA', 'NUMEROTELEFONO', 'TIPOPLAN', 'PLAN',
                        'ICC'
                    ];
                    const incompletos = camposRequeridos.some(campo => {
                        const input = document.getElementById(campo);
                        return !input || !input.value.trim();
                    });

                    if (incompletos) {
                        e.preventDefault();
                        alert(
                            "Todos los campos requeridos deben estar completos para establecer el estado como LIBRE."
                        );
                    }
                }
            });



            // Función para aplicar el estado inicial
            function aplicarEstadoInicial(estado) {
                const camposParaBloquear = {
                    ELIMINADA: ['ICC', 'ASIGNACION', 'PLATAFORMA', 'IMEI', 'EQUIPO'],
                    LIBRE: ['ASIGNACION', 'PLATAFORMA', 'IMEI', 'EQUIPO']
                };

                if (estado === 'ELIMINADA' || estado === 'LIBRE') {
                    bloquearCampos(camposParaBloquear[estado]);
                }
            }


            // Función para manejar cambios de estado
            function handleEstadoChange(estado) {
                const camposParaLimpiar = {
                    ELIMINADA: ['ICC', 'ASIGNACION', 'PLATAFORMA', 'IMEI', 'EQUIPO'],
                    LIBRE: ['ASIGNACION', 'PLATAFORMA', 'IMEI', 'EQUIPO']
                };

                if (estado === 'ELIMINADA' || estado === 'LIBRE') {
                    if (confirm(
                            `Se eliminarán los campos ${camposParaLimpiar[estado].join(', ')}. ¿Desea continuar?`
                        )) {
                        limpiarCampos(camposParaLimpiar[estado]);
                        bloquearCampos(camposParaLimpiar[estado]);
                    } else {
                        revertirEstado();
                    }
                } else if (estado === 'ACTIVA') {
                    activarTodosLosCampos();
                }
            }

            // Bloquear campos
            function bloquearCampos(campos) {
                campos.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input) {
                        input.setAttribute('readonly', true);
                        input.style.backgroundColor = '#e9ecef';
                    }
                });

                // Bloquear el select de EQUIPO
                if (campos.includes('EQUIPO')) {
                    equipoSelect.setAttribute('disabled', true);
                    equipoSelect.style.backgroundColor = '#e9ecef';
                }

                // Asegurarse de no bloquear ICC en LIBRE
                const estado = document.querySelector('input[name="ESTADO"]:checked').value;
                if (estado === 'LIBRE') {
                    const iccInput = document.getElementById('ICC');
                    if (iccInput) {
                        iccInput.removeAttribute('readonly');
                        iccInput.style.backgroundColor = '';
                    }
                }
            }

            // Activar todos los campos
            function activarTodosLosCampos() {
                const inputs = form.querySelectorAll('input, select');
                inputs.forEach(input => {
                    input.removeAttribute('readonly');
                    input.removeAttribute('disabled');
                    input.style.backgroundColor = '';
                });
            }

            // Limpiar campos
            function limpiarCampos(campos) {
                campos.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input) input.value = '';
                });
            }

            // Revertir al estado anterior si se cancela una acción
            function revertirEstado() {
                const estadoAnterior = "{{ $simcard->ESTADO }}";
                document.querySelector(`input[name="ESTADO"][value="${estadoAnterior}"]`).checked = true;
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editSimCardForm');
            const depsMigratedEl = document.getElementById('deps_migrated');
            const modalEl = document.getElementById('modalMigrarDependencias');
            const modal = new bootstrap.Modal(modalEl);
            const targetSelect = document.getElementById('target_sim_id');
            const migrarAlert = document.getElementById('migrarAlert');
            const depsResumen = document.getElementById('depsResumen');

            const simId = {{ $simcard->ID_SIM }};

            async function fetchJSON(url) {
                const resp = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                return resp.json();
            }

            async function cargarTargets() {
                targetSelect.innerHTML = '<option value="">Cargando...</option>';
                const data = await fetchJSON(`{{ route('simcards.eligibleTargets') }}?exclude=${simId}`);
                if (!data.items || data.items.length === 0) {
                    targetSelect.innerHTML = '<option value="">No hay SIMs elegibles</option>';
                    return;
                }
                targetSelect.innerHTML = '<option value="">-- Selecciona SIM destino --</option>';
                for (const it of data.items) {
                    const opt = document.createElement('option');
                    opt.value = it.id;
                    opt.textContent = it.label;
                    targetSelect.appendChild(opt);
                }
            }

            async function chequearDependenciasYMostrar(estadoNuevo) {
                try {
                    const dep = await fetchJSON(`{{ route('simcards.dependencies', $simcard->ID_SIM) }}`);
                    if (!dep.has) return true; // no tiene dependencias => continuar normal

                    // Tiene dependencias => abrir modal de migración
                    depsResumen.textContent = `Detalles: ${dep.detalles} · Documentos: ${dep.documentos}`;
                    migrarAlert.classList.add('d-none');
                    await cargarTargets();
                    modal.show();

                    return false; // detener flujo normal hasta migrar
                } catch (e) {
                    alert('Error consultando dependencias. Intenta de nuevo.');
                    console.error(e);
                    return false;
                }
            }

            async function migrarDependencias() {
                const targetId = targetSelect.value;
                if (!targetId) {
                    migrarAlert.textContent = 'Selecciona una SIM destino.';
                    migrarAlert.classList.remove('d-none');
                    return;
                }

                migrarAlert.classList.add('d-none');

                try {
                    const resp = await fetch(`{{ route('simcards.migrateDependents', $simcard->ID_SIM) }}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            target_sim_id: parseInt(targetId, 10)
                        })
                    });
                    const data = await resp.json();
                    if (!resp.ok || !data.ok) {
                        throw new Error(data.message || 'No se pudo migrar');
                    }

                    // Marcamos que ya migramos y cerramos modal
                    depsMigratedEl.value = '1';
                    modal.hide();
                    alert('Dependencias migradas correctamente. Ahora puedes guardar el cambio de estado.');

                } catch (e) {
                    migrarAlert.textContent = e.message;
                    migrarAlert.classList.remove('d-none');
                    console.error(e);
                }
            }

            document.getElementById('btnConfirmarMigracion').addEventListener('click', migrarDependencias);

            // Interceptar cambio de estado para exigir migración si hay dependencias
            form.addEventListener('change', async function(e) {
                if (e.target.name === 'ESTADO') {
                    const estadoNuevo = e.target.value;
                    if (['LIBRE', 'ELIMINADA'].includes(estadoNuevo)) {
                        // Si aún no migraste en esta sesión => chequear dependencias
                        if (depsMigratedEl.value !== '1') {
                            const ok = await chequearDependenciasYMostrar(estadoNuevo);
                            if (!ok) {
                                // Revertir radio visualmente hasta que migre
                                e.target.checked = false;
                                // Volver al estado actual
                                document.querySelector(
                                        `input[name="ESTADO"][value="{{ $simcard->ESTADO }}"]`)
                                    .checked = true;
                            }
                        }
                    }
                }
            });

            // En el submit, si va a LIBRE/ELIMINADA y hay deps_migrated=0, evitamos submit
            form.addEventListener('submit', async function(e) {
                const estado = document.querySelector('input[name="ESTADO"]:checked')?.value;
                if (['LIBRE', 'ELIMINADA'].includes(estado) && depsMigratedEl.value !== '1') {
                    e.preventDefault();
                    const ok = await chequearDependenciasYMostrar(estado);
                    if (ok) {
                        // no tenía deps, podemos intentar otra vez
                        form.submit();
                    }
                }
            });
        });
    </script>


    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    </section>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
