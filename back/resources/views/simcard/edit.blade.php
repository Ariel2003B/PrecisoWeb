@extends('layout')

@section('Titulo', 'Editar SIM Card')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Editar SIM Card</h1>
        <form action="{{ route('simcards.update', $simcard->ID_SIM) }}" method="POST">
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
            {{-- <div class="mb-3">
                <label for="RUC" class="form-label">RUC</label>
                <select name="RUC" id="RUC" class="form-control">
                    <option value="1793212253001" {{ $simcard->RUC === '1793212253001' ? 'selected' : '' }}>
                        1793212253001
                    </option>
                    <option value="1716024474001" {{ $simcard->RUC === '1716024474001' ? 'selected' : '' }}>
                        1716024474001
                    </option>
                </select>
            </div> --}}
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
                <input type="text" name="ICC" id="ICC" class="form-control" maxlength="255"
                    value="{{ old('ICC', $simcard->ICC) }}" placeholder="Ingrese el ICC">
            </div>
            <div class="mb-3">
                <label for="GRUPO" class="form-label">Grupo</label>
                <input type="text" name="GRUPO" id="GRUPO" class="form-control" maxlength="255"
                    value="{{ old('GRUPO', $simcard->GRUPO) }}" placeholder="Ingrese el Grupo">
            </div>
            <div class="mb-3">
                <label for="ASIGNACION" class="form-label">Asignacion</label>
                <input type="text" name="ASIGNACION" id="ASIGNACION" class="form-control" maxlength="255"
                    value="{{ old('ASIGNACION', $simcard->ASIGNACION) }}" placeholder="Ingrese la Asignacion">
            </div>
            <div class="mb-3">
                <label for="ESTADO" class="form-label">Estado</label>
                <select name="ESTADO" id="ESTADO" class="form-select" required>
                    <option value="ACTIVA" {{ $simcard->ESTADO === 'ACTIVA' ? 'selected' : '' }}>Activa</option>
                    <option value="INACTIVA" {{ $simcard->ESTADO === 'INACTIVA' ? 'selected' : '' }}>Inactiva</option>
                    <option value="LIBRE" {{ $simcard->ESTADO === 'LIBRE' ? 'selected' : '' }}>Libre</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
            <a href="{{ route('simcards.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
        <br>
        <br>
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
