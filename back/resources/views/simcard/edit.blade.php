@extends('layout')

@section('Titulo', 'Editar SIM Card')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Editar SIM Card</h1>
        <form action="{{ route('simcards.update', $simcard->ID_SIM) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="RUC" class="form-label">RUC</label>
                <select name="RUC" id="RUC" class="form-control">
                    <option value="1793212253001" {{ $simcard->RUC === '1793212253001' ? 'selected' : '' }}>
                        1793212253001
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <label for="PROPIETARIO" class="form-label">PROPIETARIO</label>
                <select name="PROPIETARIO" id="PROPIETARIO" class="form-control">
                    <option value="PRECISOGPS S.A.S." {{ $simcard->PROPIETARIO === 'PRECISOGPS S.A.S.' ? 'selected' : '' }}>
                        PRECISOGPS S.A.S.
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <label for="NUMEROTELEFONO" class="form-label">Número de Teléfono</label>
                <input type="text" name="NUMEROTELEFONO" id="NUMEROTELEFONO" class="form-control" maxlength="10"
                    value="{{ old('NUMEROTELEFONO', $simcard->NUMEROTELEFONO) }}" placeholder="Ingrese el número de teléfono" required>
            </div>
            <div class="mb-3">
                <label for="TIPOPLAN" class="form-label">Tipo de Plan</label>
                <input type="text" name="TIPOPLAN" id="TIPOPLAN" class="form-control" maxlength="255"
                    value="{{ old('TIPOPLAN', $simcard->TIPOPLAN) }}" placeholder="Ingrese el tipo de plan" required>
            </div>
            <div class="mb-3">
                <label for="PLAN" class="form-label">Plan</label>
                <input type="text" name="PLAN" id="PLAN" class="form-control" maxlength="255"
                    value="{{ old('PLAN', $simcard->PLAN) }}" placeholder="Ingrese el plan">
            </div>
            <div class="mb-3">
                <label for="ICC" class="form-label">ICC</label>
                <input type="text" name="ICC" id="ICC" class="form-control" maxlength="255"
                    value="{{ old('ICC', $simcard->ICC) }}" placeholder="Ingrese el ICC">
            </div>
            <div class="mb-3">
                <label for="VEH_ID" class="form-label">Vehículo Asignado</label>
                <select name="VEH_ID" id="VEH_ID" class="form-select">
                    <option value="">Sin Asignar</option>
                    @foreach ($vehiculos as $vehiculo)
                        <option value="{{ $vehiculo->VEH_ID }}"
                            {{ $simcard->VEH_ID === $vehiculo->VEH_ID ? 'selected' : '' }}>
                            {{ $vehiculo->TIPO }} - {{ $vehiculo->PLACA }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="ESTADO" class="form-label">Estado</label>
                <select name="ESTADO" id="ESTADO" class="form-select" required>
                    <option value="AC" {{ $simcard->ESTADO === 'AC' ? 'selected' : '' }}>Activo</option>
                    <option value="IN" {{ $simcard->ESTADO === 'IN' ? 'selected' : '' }}>Inactivo</option>
                    <option value="LI" {{ $simcard->ESTADO === 'LI' ? 'selected' : '' }}>Libre</option>
                    <option value="AS" {{ $simcard->ESTADO === 'AS' ? 'selected' : '' }}>Asignado</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
            <a href="{{ route('simcards.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </section>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
