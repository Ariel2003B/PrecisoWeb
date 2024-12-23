@extends('layout')

@section('Titulo', 'Agregar Vehículo')

@section('content')
<section class="container mt-5">
    <h1 class="text-center mb-4">Agregar Vehículo</h1>
    <form action="{{ route('vehiculos.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="TIPO" class="form-label">Tipo de Vehículo</label>
            <input type="text" name="TIPO" id="TIPO" class="form-control" placeholder="Ejemplo: Auto" required>
        </div>
        <div class="mb-3">
            <label for="PLACA" class="form-label">Placa</label>
            <input type="text" name="PLACA" id="PLACA" class="form-control" placeholder="Ejemplo: ABC1234" required>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</section>
@endsection
@section('jsCode', 'js/scriptNavBar.js')