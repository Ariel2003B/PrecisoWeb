@extends('layout')

@section('Titulo', 'Crear Perfil')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Crear Perfil</h1>
        <form action="{{ route('perfil.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="DESCRIPCION" class="form-label">Descripción</label>
                <input type="text" name="DESCRIPCION" id="DESCRIPCION" class="form-control"
                    placeholder="Descripcion del perfil" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Permisos</label>
                <div class="row">
                    @foreach ($permisos as $permiso)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="PERMISOS[]" id="permiso-{{ $permiso->PRM_ID }}"
                                    value="{{ $permiso->PRM_ID }}">
                                <label class="form-check-label" for="permiso-{{ $permiso->PRM_ID }}">
                                    {{ $permiso->DESCRIPCION }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="{{ route('perfil.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </section>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
