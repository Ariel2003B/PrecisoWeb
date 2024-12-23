Claro, aquí tienes una versión mejorada para el listado de usuarios con un campo de búsqueda y un ícono de ayuda para
facilitar la experiencia del usuario:

blade
Copy code
@extends('layout')

@section('Titulo', 'Listado de Usuarios')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Listado de Usuarios</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('usuario.create') }}" class="btn btn-contador">Crear Usuario</a>
            <div class="input-group" style="max-width: 400px;">
                <input type="text" id="filtro" class="form-control" placeholder="Filtrar Usuarios...">
                <button class="btn btn-secondary" type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Puedes buscar por Nombre, Apellido, Correo, Perfil o Estado.">
                    ?
                </button>
            </div>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Perfil</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->USU_ID }}</td>
                        <td>{{ $usuario->NOMBRE }}</td>
                        <td>{{ $usuario->APELLIDO }}</td>
                        <td>{{ $usuario->CORREO }}</td>
                        <td>{{ $usuario->p_e_r_f_i_l->DESCRIPCION ?? 'Sin perfil' }}</td>
                        <td>{{ $usuario->ESTADO }}</td>
                        <td>
                            <a href="{{ route('usuario.edit', $usuario->USU_ID) }}"
                                class="btn btn-contador btn-sm">Editar</a>
                            <form action="{{ route('usuario.destroy', $usuario->USU_ID) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('¿Está seguro de eliminar este usuario?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
@section('jsCode', 'js/scriptNavBar.js')
