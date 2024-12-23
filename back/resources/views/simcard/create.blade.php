@extends('layout')

@section('Titulo', 'Agregar SIM Card')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Agregar SIM Card</h1>
        <form action="{{ route('simcards.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="RUC" class="form-label">RUC</label>
                <select name="RUC" id="RUC" class="form-control">
                    <option value="1793212253001">1793212253001</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="PROPIETARIO" class="form-label">PROPIETARIO</label>
                <select name="PROPIETARIO" id="PROPIETARIO" class="form-control">
                    <option value="PRECISOGPS S.A.S.">PRECISOGPS S.A.S.</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="NUMEROTELEFONO" class="form-label">Número de Teléfono</label>
                <input type="text" name="NUMEROTELEFONO" id="NUMEROTELEFONO" class="form-control" maxlength="10"
                    value="{{ old('NUMEROTELEFONO') }}" placeholder="Ingrese el número de teléfono" required>
            </div>
            <div class="mb-3">
                <label for="TIPOPLAN" class="form-label">Tipo de Plan</label>
                <input type="text" name="TIPOPLAN" id="TIPOPLAN" class="form-control" maxlength="255"
                    value="{{ old('TIPOPLAN') }}" placeholder="Ingrese el tipo de plan" required>
            </div>
            <div class="mb-3">
                <label for="PLAN" class="form-label">Plan</label>
                <input type="text" name="PLAN" id="PLAN" class="form-control" maxlength="255"
                    value="{{ old('PLAN') }}" placeholder="Ingrese el plan">
            </div>
            <div class="mb-3">
                <label for="ICC" class="form-label">ICC</label>
                <input type="text" name="ICC" id="ICC" class="form-control" maxlength="255"
                    value="{{ old('ICC') }}" placeholder="Ingrese el ICC">
            </div>
            <div class="mb-3">
                <label for="VEH_ID" class="form-label">Vehículo Asignado</label>
                <select name="VEH_ID" id="VEH_ID" class="form-select">
                    <option value="">Sin Asignar</option>
                    @foreach ($vehiculos as $vehiculo)
                        <option value="{{ $vehiculo->VEH_ID }}">{{ $vehiculo->TIPO }} - {{ $vehiculo->PLACA }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="ESTADO" class="form-label">Estado</label>
                <select name="ESTADO" id="ESTADO" class="form-select" required>
                    <option value="AC">Activo</option>
                    <option value="IN">Inactivo</option>
                    <option value="LI">Libre</option>
                    <option value="AS">Asignado</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="{{ route('simcards.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </section>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection

@section('jsCode', 'js/scriptNavBar.js')
