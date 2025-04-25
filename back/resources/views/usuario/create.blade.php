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
                            placeholder="Apellido del usuario">
                    </div>

                    <div class="mb-3">
                        <label for="CORREO" class="form-label">Correo Electrónico</label>
                        <input type="email" name="CORREO" id="CORREO" class="form-control"
                            placeholder="Correo del usuario" required>
                    </div>

                    <div class="mb-3">
                        <label for="GENERO" class="form-label">Género</label>
                        <select name="GENERO" id="GENERO" class="form-control" required>
                            <option value="">Seleccione el género</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="CEDULA" class="form-label">Cédula</label>
                        <input type="text" name="CEDULA" id="CEDULA" class="form-control"
                            placeholder="Cédula del usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="TELEFONO" class="form-label">Telefono</label>
                        <input type="text" name="TELEFONO" id="TELEFONO" class="form-control"
                            placeholder="Telefono del usuario" required>
                    </div>

                    <div class="mb-3">
                        <label for="EMP_ID" class="form-label">Empresa</label>
                        <select name="EMP_ID" id="EMP_ID" class="form-control" required>
                            <option value="">Seleccione la empresa</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->EMP_ID }}">{{ $empresa->NOMBRE }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="TOKEN" class="form-label">Token</label>
                        <input autocomplete="off" type="text" name="TOKEN" id="TOKEN" class="form-control"
                            placeholder="Token de Nimbus para operadoras">
                    </div>

                    <div class="mb-3">
                        <label for="DEPOT" class="form-label">Depot Id</label>
                        <input autocomplete="off" type="number" name="DEPOT" id="DEPOT" class="form-control"
                            placeholder="Depot Id de Nimbus para operadoras">
                    </div>

                    <div class="mb-3">
                        <label for="CLAVE" class="form-label">Contraseña</label>
                        <input autocomplete="off" type="text" name="CLAVE" id="CLAVE" class="form-control"
                            placeholder="Contraseña" required>
                    </div>


                    <!-- Permisos -->
                    <div class="mb-3">
                        <label for="permisos" class="form-label">Permisos</label>
                        <div class="row">
                            @foreach ($permisos as $permiso)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permisos[]"
                                            value="{{ $permiso->PRM_ID }}" id="permiso{{ $permiso->PRM_ID }}">
                                        <label class="form-check-label" for="permiso{{ $permiso->PRM_ID }}">
                                            {{ $permiso->DESCRIPCION }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleRecaudo">
                        <label class="form-check-label" for="toggleRecaudo">
                            ¿Este usuario es para el aplicativo de recaudo?
                        </label>
                    </div>

                    <div class="mb-3" id="perfilAplicacion" style="display: none;">
                        <label for="PER_ID" class="form-label">Perfil aplicación</label>
                        <select name="PER_ID" id="PER_ID" class="form-control">
                            <option value="8">Administrador</option>
                            <option value="9">Conductor</option>
                            <option value="10">Accionista</option>
                        </select>
                    </div>

                    <input type="hidden" name="es_recaudo" id="es_recaudo" value="0">

                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="{{ route('usuario.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('toggleRecaudo');
            const perfilBox = document.getElementById('perfilAplicacion');
            const esRecaudoInput = document.getElementById('es_recaudo');
            const selectPerfil = document.getElementById('PER_ID');

            toggle.addEventListener('change', function() {
                const activo = this.checked;
                perfilBox.style.display = activo ? 'block' : 'none';
                esRecaudoInput.value = activo ? '1' : '0';

                if (!activo) {
                    // Eliminar el atributo name para que NO se envíe
                    selectPerfil.removeAttribute('name');
                } else {
                    // Restaurar el atributo name
                    selectPerfil.setAttribute('name', 'PER_ID');
                }
            });

            // Asegura que si al cargar la página el switch está apagado, no se envíe PER_ID
            if (!toggle.checked) {
                selectPerfil.removeAttribute('name');
            }
        });
    </script>

    {{-- <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const toggle = document.getElementById('toggleRecaudo');
            if (!toggle.checked) {
                const perIdSelect = document.getElementById('PER_ID');
                if (perIdSelect) {
                    perIdSelect.disabled = true;
                }
            }
        });
    </script> --}}

@endsection

@section('jsCode', 'js/scriptNavBar.js')
