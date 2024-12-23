@extends('layout')

@section('Titulo', 'Gestión de SIM Cards')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Lista de SIM Cards</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('simcards.create') }}" class="btn btn-contador">Agregar SIM Card</a>
            <div class="input-group" style="max-width: 400px;">
                <input type="text" id="filtro" class="form-control" placeholder="Filtrar SIM Cards...">
                <button class="btn btn-contador" type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Puedes buscar por cualquier dato visible en la tabla, como Número, Propietario, Plan, ICC o Vehículo.">
                    ?
                </button>
            </div>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Propietario</th>
                    <th>Plan</th>
                    <th>ICC</th>
                    <th>Vehículo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($simcards as $simcard)
                    <tr>
                        <td>{{ $simcard->ID_SIM }}</td>
                        <td>{{ $simcard->NUMEROTELEFONO }}</td>
                        <td>{{ $simcard->PROPIETARIO }}</td>
                        <td>{{ $simcard->TIPOPLAN }}</td>
                        <td>{{ $simcard->ICC }}</td>
                        <td>{{ $simcard->v_e_h_i_c_u_l_o->PLACA ?? 'Sin Asignar' }}</td>
                        <td>
                            <a href="{{ route('simcards.edit', $simcard->ID_SIM) }}"
                                class="btn btn-contador btn-sm">Editar</a>
                            <form action="{{ route('simcards.destroy', $simcard->ID_SIM) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
