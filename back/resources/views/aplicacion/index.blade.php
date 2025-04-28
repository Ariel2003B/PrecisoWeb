@extends('layout')

@section('Titulo', 'Administración de Aplicación')

@section('content')
<main class="main">
    <div class="page-title accent-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Administración de Aplicación</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                    <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                    <li class="current">Administración de Aplicación</li>
                </ol>
            </nav>
        </div>
    </div><!-- End Page Title -->

    <section class="section d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="container" style="max-width: 600px;">
            <div class="d-flex flex-column gap-4">
                <!-- Gestión de Unidades -->
                <a href="{{route('unidades.index')}}" class="text-decoration-none">
                    <div class="card p-4 shadow menu-card text-center border-0 rounded-4">
                        <div class="icon-circle bg-primary mb-4">
                            <i class="bi bi-truck" style="font-size: 2.5rem; color: white;"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-2">Gestión de Unidades</h5>
                    </div>
                </a>

                <!-- Gestión de Rutas -->
                <a href="{{route('rutasapp.index')}}" class="text-decoration-none">
                    <div class="card p-4 shadow menu-card text-center border-0 rounded-4">
                        <div class="icon-circle bg-success mb-4">
                            <i class="bi bi-geo-alt-fill" style="font-size: 2.5rem; color: white;"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-2">Gestión de Rutas</h5>
                    </div>
                </a>

                <!-- Asignación de Unidades -->
                <a href="{{route('asignacion.index')}}" class="text-decoration-none">
                    <div class="card p-4 shadow menu-card text-center border-0 rounded-4">
                        <div class="icon-circle bg-danger mb-4">
                            <i class="bi bi-person-badge-fill" style="font-size: 2.5rem; color: white;"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-2">Asignación de Unidades a Accionistas</h5>
                    </div>
                </a>
            </div>
        </div>
    </section>
</main>

<style>
    .menu-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background-color: #ffffff;
        border: 1px solid #dee2e6;
    }

    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .icon-circle {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin: 0 auto;
    }
</style>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
