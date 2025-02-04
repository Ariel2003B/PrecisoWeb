@extends('layout')

@section('Titulo', 'Crear Perfil')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Crear Perfil</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('perfil.index') }}">Perfiles</a></li>
                        <li class="current">Crear Perfil</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="container">
                <form action="{{ route('perfil.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="DESCRIPCION" class="form-label">Descripción</label>
                        <input type="text" name="DESCRIPCION" id="DESCRIPCION" class="form-control"
                            placeholder="Descripción del perfil" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Permisos</label>
                        <div class="row">
                            @foreach ($permisos as $permiso)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="PERMISOS[]"
                                            id="permiso-{{ $permiso->PRM_ID }}" value="{{ $permiso->PRM_ID }}">
                                        <label class="form-check-label" for="permiso-{{ $permiso->PRM_ID }}">
                                            {{ $permiso->DESCRIPCION }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Botón para abrir el modal de creación de permisos -->
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                        data-bs-target="#crearPermisoModal">
                        <i class="fas fa-plus"></i> Crear nuevo permiso
                    </button>

                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="{{ route('perfil.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </section>
    </main>

    <!-- Modal para crear nuevo permiso -->
    <div class="modal fade" id="crearPermisoModal" tabindex="-1" aria-labelledby="crearPermisoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearPermisoModalLabel">Crear Nuevo Permiso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('permiso.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nuevoPermiso" class="form-label">Descripción del Permiso</label>
                            <input type="text" class="form-control" id="nuevoPermiso" name="DESCRIPCION" required>
                        </div>
                        <button type="submit" class="btn btn-success">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
