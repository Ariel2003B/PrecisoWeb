@extends('layout')

@section('Titulo', 'Crear Empresa')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Crear Empresa</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('empresa.index') }}">Empresas</a></li>
                        <li class="current">Crear Empresa</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="container">
                <form action="{{ route('empresa.store') }}" method="POST" enctype="multipart/form-data">

                    @csrf

                    <div class="mb-3">
                        <label for="NOMBRE" class="form-label">Nombre</label>
                        <input type="text" name="NOMBRE" id="NOMBRE" class="form-control"
                            placeholder="Nombre de la Empresa" required>
                    </div>

                    <div class="mb-3">
                        <label for="RUC" class="form-label">RUC</label>
                        <input type="text" name="RUC" id="RUC" class="form-control"
                            placeholder="RUC de la Empresa" required>
                    </div>

                    <div class="mb-3">
                        <label for="DIRECCION" class="form-label">Dirección</label>
                        <input type="text" name="DIRECCION" id="DIRECCION" class="form-control"
                            placeholder="Dirección de la Empresa">
                    </div>

                    <div class="mb-3">
                        <label for="TELEFONO" class="form-label">Teléfono</label>
                        <input type="text" name="TELEFONO" id="TELEFONO" class="form-control"
                            placeholder="Teléfono de la Empresa">
                    </div>

                    <div class="mb-3">
                        <label for="CORREO" class="form-label">Correo Electrónico</label>
                        <input type="email" name="CORREO" id="CORREO" class="form-control"
                            placeholder="Correo de la Empresa">
                    </div>

                    <div class="mb-3">
                        <label for="ESTADO" class="form-label">Estado</label>
                        <select name="ESTADO" id="ESTADO" class="form-control" required>
                            <option value="A">Activo</option>
                            <option value="I">Inactivo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="IMAGEN" class="form-label">Logo o Imagen de la Empresa</label>
                        <input type="file" name="IMAGEN" id="IMAGEN" class="form-control" accept="image/*">
                    </div>
                    

                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="{{ route('empresa.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </section>
    </main>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
