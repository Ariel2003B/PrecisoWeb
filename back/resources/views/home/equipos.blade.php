@extends('layout')
@section('Titulo', 'PrecisoGPS - Equipos y Accesorios')
@section('ActivarEquipos', 'active')

@section('content')
    <main class="main">
        <!-- Page Title -->
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Equipos y Accesorios</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Equipos y Accesorios</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <!-- Equipos y Accesorios Section -->
        <section id="equipos" class="equipos section">
            <div class="container">
                <div class="row gy-4">
                    @foreach ($equipos as $equipo)
                        <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100">
                            <div class="mi-card shadow-sm p-3 rounded text-center h-100">
                                <div class="icon-card mb-3">
                                    <i class="{{ $equipo->EQU_ICONO }} fs-1 text-primary"></i>
                                </div>
                                <h4 class="fw-bold">{{ $equipo->EQU_NOMBRE }}</h4>
                                <h5 class="text-success"><sup>$</sup>{{ number_format($equipo->EQU_PRECIO, 2) }}</h5>
                                <button class="btn btn-primary add-to-cart mt-2" data-id="{{ $equipo->EQU_ID }}"
                                    data-name="{{ $equipo->EQU_NOMBRE }}" data-price="{{ $equipo->EQU_PRECIO }}">
                                    Añadir al carrito
                                </button>
                            </div>
                        </div><!-- End Equipo Item -->
                    @endforeach
                </div>
            </div>
        </section><!-- /Equipos y Accesorios Section -->
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();

                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const price = this.dataset.price;

                    fetch("{{ route('carrito.add') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                id: id,
                                name: name,
                                price: price
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('cart-count').textContent = data.cartCount;
                            alert(data.message);
                        })
                        .catch(error => console.error("Error:", error));
                });
            });
        });
    </script>
@endsection
