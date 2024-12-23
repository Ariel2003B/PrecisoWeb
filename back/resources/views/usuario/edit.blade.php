@extends('layout')

@section('Titulo', 'Editar Usuario')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Editar Usuario</h1>
        <form action="{{ route('usuario.update', $usuario->USU_ID) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="NOMBRE" class="form-label">Nombre</label>
                <input type="text" name="NOMBRE" id="NOMBRE" class="form-control" 
                    value="{{ old('NOMBRE', $usuario->NOMBRE) }}" required>
            </div>
            <div class="mb-3">
                <label for="APELLIDO" class="form-label">Apellido</label>
                <input type="text" name="APELLIDO" id="APELLIDO" class="form-control" 
                    value="{{ old('APELLIDO', $usuario->APELLIDO) }}" required>
            </div>
            <div class="mb-3">
                <label for="CORREO" class="form-label">Correo Electrónico</label>
                <input type="email" name="CORREO" id="CORREO" class="form-control" 
                    value="{{ old('CORREO', $usuario->CORREO) }}" required>
            </div>
            <div class="mb-3">
                <label for="CLAVE" class="form-label">Contraseña</label>
                <input type="password" name="CLAVE" id="CLAVE" class="form-control" placeholder="Nueva contraseña (opcional)">
            </div>
            <div class="mb-3">
                <label for="PER_ID" class="form-label">Perfil</label>
                <select name="PER_ID" id="PER_ID" class="form-select" required>
                    @foreach ($perfiles as $perfil)
                        <option value="{{ $perfil->PER_ID }}" 
                            {{ $usuario->PER_ID == $perfil->PER_ID ? 'selected' : '' }}>
                            {{ $perfil->DESCRIPCION }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success">Actualizar</button>
            <a href="{{ route('usuario.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </section>
@endsection
@section('jsCode', 'js/scriptNavBar.js')
