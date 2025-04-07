@extends('layout')

@section('Titulo', 'Editar Usuario')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Editar Usuario</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('usuario.index') }}">Usuarios</a></li>
                        <li class="current">Editar Usuario</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="container">
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
                            value="{{ old('APELLIDO', $usuario->APELLIDO) }}">
                    </div>

                    <div class="mb-3">
                        <label for="CORREO" class="form-label">Correo Electrónico</label>
                        <input type="email" name="CORREO" id="CORREO" class="form-control"
                            value="{{ old('CORREO', $usuario->CORREO) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="GENERO" class="form-label">Género</label>
                        <select name="GENERO" id="GENERO" class="form-control" required>
                            <option value="">Seleccione el género</option>
                            <option value="Masculino"
                                {{ old('GENERO', $usuario->GENERO) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                            <option value="Femenino" {{ old('GENERO', $usuario->GENERO) == 'Femenino' ? 'selected' : '' }}>
                                Femenino</option>
                            <option value="Otro" {{ old('GENERO', $usuario->GENERO) == 'Otro' ? 'selected' : '' }}>Otro
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="CEDULA" class="form-label">Cédula</label>
                        <input type="text" name="CEDULA" id="CEDULA" class="form-control"
                            value="{{ old('CEDULA', $usuario->CEDULA) }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="TELEFONO" class="form-label">Telefono</label>
                        <input type="text" name="TELEFONO" id="TELEFONO" class="form-control"
                            placeholder="Telefono del usuario" required value="{{ old('TELEFONO', $usuario->TELEFONO) }}">
                    </div>

                    <div class="mb-3">
                        <label for="EMP_ID" class="form-label">Empresa</label>
                        <select name="EMP_ID" id="EMP_ID" class="form-control">
                            <option value="">Seleccione la empresa</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->EMP_ID }}"
                                    {{ old('EMP_ID', $usuario->EMP_ID) == $empresa->EMP_ID ? 'selected' : '' }}>
                                    {{ $empresa->NOMBRE }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="TOKEN" class="form-label">Token</label>
                        <input type="text" name="TOKEN" id="TOKEN" class="form-control"
                            value="{{ old('TOKEN', $usuario->TOKEN) }}">
                    </div>

                    <div class="mb-3">
                        <label for="DEPOT" class="form-label">Depot Id</label>
                        <input type="text" name="DEPOT" id="DEPOT" class="form-control"
                            value="{{ old('DEPOT', $usuario->DEPOT) }}" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label for="CLAVE" class="form-label">Contraseña</label>
                        <input type="text" autocomplete="off" name="CLAVE" id="CLAVE" class="form-control"
                            placeholder="Nueva contraseña (opcional)">
                    </div>

                    <div class="mb-3">
                        <label for="ESTADO" class="form-label">Estado</label>
                        <select name="ESTADO" id="EMP_ID" class="form-control">

                            <option value="A">Activo
                            </option>
                            <option value="I">Inactivo</option>
                        </select>
                    </div>
                    <!-- Permisos -->
                    <div class="mb-3">
                        <label for="permisos" class="form-label">Permisos</label>
                        <div class="row">
                            @foreach ($permisos as $permiso)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permisos[]"
                                            value="{{ $permiso->PRM_ID }}" id="permiso{{ $permiso->PRM_ID }}"
                                            @if (in_array($permiso->PRM_ID, $usuarioPermisos)) checked @endif>
                                        <label class="form-check-label" for="permiso{{ $permiso->PRM_ID }}">
                                            {{ $permiso->DESCRIPCION }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Actualizar</button>
                    <a href="{{ route('usuario.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </section>
    </main>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
