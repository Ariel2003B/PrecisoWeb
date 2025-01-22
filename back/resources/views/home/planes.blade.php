@extends('layout')
@section('Titulo', 'PrecisoGPS - Planes')
@section('ActivarPlanes', 'active')
@section('content')
    <main class="main">

        <!-- Page Title -->
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Planes</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Planes</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <!-- Pricing Section -->
        <section id="pricing" class="pricing section">

            <div class="container">

                <div class="row gy-4">
                    @foreach ($planes as $plan)
                        <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="100">
                            <div class="pricing-item">
                                <h3>{{ $plan->NOMBRE }}</h3>
                                <p class="description">{{ $plan->DESCRIPCION }}</p>
                                <h4><sup>$</sup>{{ $plan->PRECIO }}<span> /{{ $plan->TIEMPO }} Meses </span></h4>
                                <a href="#" class="cta-btn add-to-cart" data-id="{{ $plan->PLA_ID }}"
                                    data-name="{{ $plan->NOMBRE }}" data-price="{{ $plan->PRECIO }}"
                                    data-time="{{ $plan->TIEMPO }}">Añadir al carrito</a>

                                <ul>
                                    @foreach ($plan->c_a_r_a_c_t_e_r_i_s_t_i_c_a_s as $caracteristica)
                                        @if ($caracteristica->pivot->POSEE)
                                            <li><i class="bi bi-check"></i> <span>{{ $caracteristica->DESCRIPCION }}</span>
                                            </li>
                                        @else
                                            <li class="na"><i class="bi bi-x"></i>
                                                <span>{{ $caracteristica->DESCRIPCION }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div><!-- End Pricing Item -->
                    @endforeach
                </div>
            </div>
        </section><!-- /Pricing Section -->
    </main>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    debugger;
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const price = this.dataset.price;
                    const time = this.dataset.time;

                    fetch('carrito/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                id,
                                name,
                                price,
                                time
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('cart-count').textContent = data.cartCount;
                            alert(data.message);
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
@endsection
