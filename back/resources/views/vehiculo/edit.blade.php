@extends('layout')

@section('Titulo', 'Editar Vehículo')

@section('content')
<section class="container mt-5">
    <h1 class="text-center mb-4">Editar Vehículo</h1>
    <form action="{{ route('vehiculos.update', $vehiculo->VEH_ID) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="TIPO" class="form-label">Tipo de Vehículo</label>
            <input type="text" name="TIPO" id="TIPO" class="form-control" value="{{ $vehiculo->TIPO }}" required>
        </div>
        <div class="mb-3">
            <label for="PLACA" class="form-label">Placa</label>
            <input type="text" name="PLACA" id="PLACA" class="form-control" value="{{ $vehiculo->PLACA }}" required>
        </div>
        <button type="submit" class="btn btn-success">Actualizar</button>
        <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</section>
@endsection
@section('jsCode', 'js/scriptNavBar.js')
