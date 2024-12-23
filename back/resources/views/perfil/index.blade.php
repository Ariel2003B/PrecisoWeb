@extends('layout')

@section('Titulo', 'Gestión de Perfiles')

@section('content')
<section class="container mt-5">
    <h1 class="text-center mb-4">Gestión de Perfiles</h1>
    <a href="{{ route('perfil.create') }}" class="btn btn-contador mb-3">Crear Nuevo Perfil</a>

    <table class="table table-bordered table-hover">
        <thead class="bg-dark text-white text-center">
            <tr>
                <th>#</th>
                <th>Descripción</th>
                <th>Permisos</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($perfiles as $perfil)
            <tr>
                <td>{{ $perfil->PER_ID }}</td>
                <td>{{ $perfil->DESCRIPCION }}</td>
                <td>
                    @foreach($perfil->p_e_r_m_i_s_o_s as $permiso)
                    <span class="badge bg-success">{{ $permiso->DESCRIPCION }}</span>
                    @endforeach
                </td>
                <td>{{ $perfil->ESTADO === 'A' ? 'Activo' : 'Inactivo' }}</td>
                <td class="text-center">
                    <a href="{{ route('perfil.edit', $perfil->PER_ID) }}" class="btn btn-contador btn-sm">Editar</a>
                    <form action="{{ route('perfil.destroy', $perfil->PER_ID) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No hay perfiles registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
@section('jsCode', 'js/scriptNavBar.js')
