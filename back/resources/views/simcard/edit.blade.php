@extends('layout')

@section('Titulo', 'Editar SIM Card')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Editar SIM Card</h1>
        <form action="{{ route('simcards.update', $simcard->ID_SIM) }}" id="editSimCardForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="PROPIETARIO" class="form-label">PROPIETARIO</label>
                <select name="PROPIETARIO" id="PROPIETARIO" class="form-control">
                    <option value="PRECISOGPS S.A.S." {{ $simcard->PROPIETARIO === 'PRECISOGPS S.A.S.' ? 'selected' : '' }}>
                        PRECISOGPS S.A.S.
                    </option>
                    <option value="VARGAS REINOSO CESAR GIOVANNY"
                        {{ $simcard->PROPIETARIO === 'VARGAS REINOSO CESAR GIOVANNY' ? 'selected' : '' }}>
                        VARGAS REINOSO CESAR GIOVANNY
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <label for="CUENTA" class="form-label">Cuenta</label>
                <input type="text" name="CUENTA" id="CUENTA" class="form-control"
                    value="{{ old('CUENTA', $simcard->CUENTA) }}" placeholder="Ingrese la cuenta de la SIM" required>
            </div>
            <div class="mb-3">
                <label for="NUMEROTELEFONO" class="form-label">Número de Teléfono</label>
                <input type="text" name="NUMEROTELEFONO" id="NUMEROTELEFONO" class="form-control" maxlength="10"
                    value="{{ old('NUMEROTELEFONO', $simcard->NUMEROTELEFONO) }}"
                    placeholder="Ingrese el número de teléfono" required>
            </div>
            <div class="mb-3">
                <label for="TIPOPLAN" class="form-label">Codigo de Plan</label>
                <input type="text" name="TIPOPLAN" id="TIPOPLAN" class="form-control" maxlength="255"
                    value="{{ old('TIPOPLAN', $simcard->TIPOPLAN) }}" placeholder="Ingrese el codigo de plan" required>
            </div>
            <div class="mb-3">
                <label for="PLAN" class="form-label">Plan</label>
                <input type="text" name="PLAN" id="PLAN" class="form-control" maxlength="255"
                    value="{{ old('PLAN', $simcard->PLAN) }}" placeholder="Ingrese el plan">
            </div>

            <div class="mb-3">
                <label for="ICC" class="form-label">ICC</label>
                <input type="text" name="ICC" id="ICC" class="form-control"
                    value="{{ old('ICC', $simcard->ICC) }}" placeholder="Ingrese el ICC">
            </div>
            <div class="mb-3">
                <label for="GRUPO" class="form-label">Grupo</label>
                <input type="text" name="GRUPO" id="GRUPO" class="form-control"
                    value="{{ old('GRUPO', $simcard->GRUPO) }}" placeholder="Ingrese el Grupo">
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
                    <option value="COMPUTADOR ABORDO" {{ $simcard->EQUIPO === 'COMPUTADOR ABORDO' ? 'selected' : '' }}>COMPUTADOR ABORDO</option>
                    <option value="LECTOR DE QR" {{ $simcard->EQUIPO === 'LECTOR DE QR' ? 'selected' : '' }}>LECTOR DE QR</option>
                </select>
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

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('editSimCardForm');
                const estadoInicial = "{{ $simcard->ESTADO }}";
                const equipoSelect = document.getElementById('EQUIPO');
        
                // Bloquear automáticamente campos al cargar según el estado inicial
                aplicarEstadoInicial(estadoInicial);
        
                // Detectar cambios en los radio buttons
                form.addEventListener('change', function (e) {
                    if (e.target.name === 'ESTADO') {
                        handleEstadoChange(e.target.value);
                    }
                });
        
                // Validar antes de enviar el formulario
                form.addEventListener('submit', function (e) {
                    const estado = document.querySelector('input[name="ESTADO"]:checked').value;
                    if (estado === 'ACTIVA') {
                        const camposRequeridos = ['PROPIETARIO', 'CUENTA', 'NUMEROTELEFONO', 'TIPOPLAN', 'PLAN', 'ICC', 'EQUIPO'];
                        const incompletos = camposRequeridos.some(campo => {
                            const input = document.getElementById(campo);
                            return !input || !input.value.trim();
                        });
        
                        if (incompletos) {
                            e.preventDefault();
                            alert("Todos los campos requeridos deben estar completos para establecer el estado como ACTIVA.");
                        }
                    }
                    if(estado==='LIBRE'){
                        const camposRequeridos = ['PROPIETARIO', 'CUENTA', 'NUMEROTELEFONO', 'TIPOPLAN', 'PLAN', 'ICC'];
                        const incompletos = camposRequeridos.some(campo => {
                            const input = document.getElementById(campo);
                            return !input || !input.value.trim();
                        });
        
                        if (incompletos) {
                            e.preventDefault();
                            alert("Todos los campos requeridos deben estar completos para establecer el estado como LIBRE.");
                        }
                    }
                });


        
                // Función para aplicar el estado inicial
                function aplicarEstadoInicial(estado) {
                    const camposParaBloquear = {
                        ELIMINADA: ['ICC', 'GRUPO', 'ASIGNACION', 'IMEI', 'EQUIPO'],
                        LIBRE: ['GRUPO', 'ASIGNACION', 'IMEI', 'EQUIPO']
                    };
        
                    if (estado === 'ELIMINADA' || estado === 'LIBRE') {
                        bloquearCampos(camposParaBloquear[estado]);
                    }
                }
        
                // Función para manejar cambios de estado
                function handleEstadoChange(estado) {
                    const camposParaLimpiar = {
                        ELIMINADA: ['ICC', 'GRUPO', 'ASIGNACION', 'IMEI', 'EQUIPO'],
                        LIBRE: ['GRUPO', 'ASIGNACION', 'IMEI', 'EQUIPO']
                    };
        
                    if (estado === 'ELIMINADA' || estado === 'LIBRE') {
                        if (confirm(`Se eliminarán los campos ${camposParaLimpiar[estado].join(', ')}. ¿Desea continuar?`)) {
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
