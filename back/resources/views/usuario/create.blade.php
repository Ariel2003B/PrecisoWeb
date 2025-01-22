@extends('layout')

@section('Titulo', 'Crear Usuario')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Crear Usuario</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('usuario.index') }}">Usuarios</a></li>
                        <li class="current">Crear Usuario</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="container">
                <form action="{{ route('usuario.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="NOMBRE" class="form-label">Nombre</label>
                        <input type="text" name="NOMBRE" id="NOMBRE" class="form-control"
                            placeholder="Nombre del usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="APELLIDO" class="form-label">Apellido</label>
                        <input type="text" name="APELLIDO" id="APELLIDO" class="form-control"
                            placeholder="Apellido del usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="CORREO" class="form-label">Correo Electrónico</label>
                        <input type="email" name="CORREO" id="CORREO" class="form-control"
                            placeholder="Correo del usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="CLAVE" class="form-label">Contraseña</label>
                        <input type="password" name="CLAVE" id="CLAVE" class="form-control" placeholder="Contraseña"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="PER_ID" class="form-label">Perfil</label>
                        <select name="PER_ID" id="PER_ID" class="form-select" required>
                            @foreach ($perfiles as $perfil)
                                <option value="{{ $perfil->PER_ID }}">{{ $perfil->DESCRIPCION }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="{{ route('usuario.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </section>
    </main>
@endsection
@section('jsCode', 'js/scriptNavBar.js')
