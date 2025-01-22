@extends('layout')

@section('Titulo', 'Carrito de Compras')

@section('content')
    <main class="main">
        <!-- Page Title -->
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Carrito</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Carrito</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="container">
                <h1 class="mb-4 text-center">Carrito de Compras</h1>
                @if (empty($carrito))
                    <div class="text-center">
                        <p class="text-center">No hay planes en tu carrito.</p>
                        <a href="{{ route('home.planes') }}" class="btn btn-primary btn-lg mt-3">Explorar Planes de
                            Rastreo</a>
                    </div>
                @else
                    <table class="table table-bordered text-center">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Duración</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0; @endphp
                            @foreach ($carrito as $item)
                                @php $subtotal = $item['price'] * $item['quantity']; @endphp
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>${{ number_format($item['price'], 2) }}</td>
                                    <td>{{ $item['time'] }} meses</td>
                                    <td>
                                        <input type="number"
                                            class="form-control form-control-sm quantity-update w-50 mx-auto"
                                            data-id="{{ $item['id'] }}" value="{{ $item['quantity'] }}" min="1" />
                                    </td>
                                    <td>${{ number_format($subtotal, 2) }}</td>
                                    <td>
                                        <form action="{{ route('carrito.remove', $item['id']) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                @php $total += $subtotal; @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total:</td>
                                <td colspan="2" class="fw-bold">${{ number_format($total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <form action="{{ route('carrito.clear') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-warning btn-lg">Vaciar Carrito</button>
                        </form>
                        {{-- <a href="{{ route('pago.iniciar') }}" class="btn btn-success btn-lg">Proceder con el Pago</a> --}}
                        <a href="#" id="whatsapp-button" class="btn btn-success btn-lg">Enviar a WhatsApp</a>
                    </div>
                @endif
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const whatsappButton = document.getElementById('whatsapp-button');
            let carrito = @json($carrito); // Pasar el carrito desde el backend al frontend

            // Convertimos el carrito en un arreglo si es un objeto
            carrito = Object.values(carrito);

            const numeroWhatsApp =
                '+593990453275'; // Reemplaza con el número de WhatsApp al que deseas enviar los datos

            whatsappButton.addEventListener('click', function(event) {
                event.preventDefault();

                let mensaje = '*¡Hola! Estoy interesado en realizar una compra.*\n\n';
                mensaje += '*Detalle de mi pedido:*\n\n';
                let total = 0;

                carrito.forEach(item => {
                    const price = parseFloat(item.price); // Aseguramos que el precio sea numérico
                    const subtotal = price * item.quantity;
                    mensaje += `➡️ *Producto:* ${item.name}\n`;
                    mensaje += `   - *Precio unitario:* $${price.toFixed(2)}\n`;
                    mensaje += `   - *Cantidad:* ${item.quantity}\n`;
                    mensaje += `   - *Subtotal:* $${subtotal.toFixed(2)}\n\n`;
                    total += subtotal;
                });

                mensaje += `*Total a pagar:* $${total.toFixed(2)}\n\n`;
                mensaje += 'Por favor, indíquenme los pasos para completar mi pedido. ¡Gracias!';

                const url = `https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(mensaje)}`;
                window.open(url, '_blank');
            });
        });




        document.addEventListener('DOMContentLoaded', function() {
            const quantityInputs = document.querySelectorAll('.quantity-update');
            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const id = this.dataset.id;
                    const quantity = this.value;

                    fetch('/carrito/update-quantity', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                id,
                                quantity
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            location.reload(); // Recarga la página para actualizar el total
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
@endsection
