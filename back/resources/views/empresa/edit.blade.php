@extends('layout')

@section('Titulo', 'Editar Empresa')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Editar Empresa</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current"><a href="{{ route('empresa.index') }}">Empresas</a></li>
                        <li class="current">Editar Empresa</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="container">
                <form action="{{ route('empresa.update', $empresa->EMP_ID) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="NOMBRE" class="form-label">Nombre</label>
                        <input type="text" name="NOMBRE" id="NOMBRE" class="form-control"
                            value="{{ old('NOMBRE', $empresa->NOMBRE) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="RUC" class="form-label">RUC</label>
                        <input type="text" name="RUC" id="RUC" class="form-control"
                            value="{{ old('RUC', $empresa->RUC) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="DIRECCION" class="form-label">Dirección</label>
                        <input type="text" name="DIRECCION" id="DIRECCION" class="form-control"
                            value="{{ old('DIRECCION', $empresa->DIRECCION) }}">
                    </div>

                    <div class="mb-3">
                        <label for="TELEFONO" class="form-label">Teléfono</label>
                        <input type="text" name="TELEFONO" id="TELEFONO" class="form-control"
                            value="{{ old('TELEFONO', $empresa->TELEFONO) }}">
                    </div>

                    <div class="mb-3">
                        <label for="CORREO" class="form-label">Correo Electrónico</label>
                        <input type="email" name="CORREO" id="CORREO" class="form-control"
                            value="{{ old('CORREO', $empresa->CORREO) }}">
                    </div>
                    <div class="mb-3">
                        <label for="TOKEN" class="form-label">Token nimbus</label>
                        <input type="text" name="TOKEN" id="TOKEN" class="form-control"
                           value="{{ old('TOKEN', $empresa->TOKEN) }}" placeholder="Token obtenido de nimbus">
                    </div>
                    <div class="mb-3">
                        <label for="DEPOT" class="form-label">Depot</label>
                        <input type="number" name="DEPOT" id="DEPOT" class="form-control"
                           value="{{ old('DEPOT', $empresa->DEPOT) }}" placeholder="Llena solo administrador">
                    </div>
                    <div class="mb-3">
                        <label for="ESTADO" class="form-label">Estado</label>
                        <select name="ESTADO" id="ESTADO" class="form-control" required>
                            <option value="A" {{ old('ESTADO', $empresa->ESTADO) == 'A' ? 'selected' : '' }}>Activo
                            </option>
                            <option value="I" {{ old('ESTADO', $empresa->ESTADO) == 'I' ? 'selected' : '' }}>Inactivo
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="IMAGEN" class="form-label">Logo o Imagen de la Empresa</label>
                        <input type="file" name="IMAGEN" id="IMAGEN" class="form-control" accept="image/*">
                    </div>


                    <button type="submit" class="btn btn-success">Actualizar</button>
                    <a href="{{ route('empresa.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </section>
    </main>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
