@extends('layout')

@section('Titulo', $perfil->exists ? 'Editar Perfil' : 'Crear Perfil')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">{{ $perfil->exists ? 'Editar Perfil' : 'Crear Perfil' }}</h1>
        <form action="{{ $perfil->exists ? route('perfil.update', $perfil->PER_ID) : route('perfil.store') }}" method="POST">
            @csrf
            @if ($perfil->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="DESCRIPCION" class="form-label">Descripción</label>
                <input type="text" name="DESCRIPCION" id="DESCRIPCION" class="form-control"
                    value="{{ old('DESCRIPCION', $perfil->DESCRIPCION) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Permisos</label>
                <div class="row">
                    @foreach ($permisos as $permiso)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="PERMISOS[]"
                                    id="permiso-{{ $permiso->PRM_ID }}" value="{{ $permiso->PRM_ID }}"
                                    {{ $perfil->p_e_r_m_i_s_o_s->contains($permiso->PRM_ID) ? 'checked' : '' }}>
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
