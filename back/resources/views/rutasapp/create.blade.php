@extends('layout')

@section('Titulo', 'Crear Ruta')

@section('content')
<main class="main">
    <div class="page-title accent-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Crear Ruta</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                    <li><a href="{{ route('rutasapp.index') }}">Rutas</a></li>
                    <li class="current">Crear</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section py-5">
        <div class="container">
            <form action="{{ route('rutasapp.store') }}" method="POST">
                @csrf

                @include('rutasapp.partials.form')

                <button type="submit" class="btn btn-success mt-3">Guardar</button>
            </form>
        </div>
    </section>
</main>
@endsection

@section('jsCode')
<script>
    $(document).ready(function() {
        $('#EMP_ID').select2({
            placeholder: 'Selecciona una empresa',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
