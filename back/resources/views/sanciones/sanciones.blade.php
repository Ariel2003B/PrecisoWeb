@extends('layout')

@section('Titulo', 'Sanciones')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Reporte de sanciones</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current"><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current">Sanciones</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="container">
                <form action="{{ route('sanciones.cargarCSV') }}" method="POST" enctype="multipart/form-data"
                    class="mb-4">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="archivo" id="archivo" accept=".csv" required class="form-control">
                        <button class="btn btn-success" type="submit">
                            <i class="bi bi-cloud-upload"></i> Cargar datos
                        </button>
                    </div>
                </form>
                @if (isset($datos) && isset($geocercas))
                    {{-- Tabla de sanciones procesadas --}}
                    @if (!empty($detalles))
                        <h3 class="text-center mb-4">
                            Sanciones Procesadas de la ruta {{ $detalles['ruta'] ?? 'No disponible' }}
                        </h3>
                        <h5 class="my-3">
                            Fecha: {{ $detalles['fecha'] ?? 'No disponible' }}
                        </h5>
                    @endif


                    <table id="tablaSanciones" class="table table-striped table-bordered text-center align-middle">
                        <thead class="sticky-top">
                            <tr>
                                <th class="geocerca">Vuelta</th>
                                <th class="geocerca">Unidad</th>
                                {{-- <th class="geocerca">Placa</th> --}}
                                <th class="geocerca">Hora salida</th>
                                @foreach ($geocercas as $geocerca)
                                    <th class="geocerca">{{ $geocerca }}</th>
                                @endforeach
                                <th class="geocerca">Total</th>
                                <th class="geocerca">Valor Total</th>
                                <th class="geocerca"> <input type="checkbox" id="checkAll"> Seleccionar</th>

                            </tr>
                        </thead>


                        <tbody>
                            @foreach ($datos as $key => $dato)
                                <tr data-unidad="{{ $dato['unidad'] }}" data-vuelta="{{ $dato['vuelta'] }}">
                                    <td>{{ $dato['vuelta'] }}</td>
                                    <td>{{ $dato['unidad'] }}</td>
                                    {{-- <td>{{ $dato['placa'] }}</td> --}}
                                    <td>{{ $dato['hora'] }}</td>
                                    @foreach ($dato['sanciones'] as $sancion)
                                        <td>{{ $sancion }}</td>
                                    @endforeach
                                    <td class="total-sanciones">{{ $dato['total'] }}</td>
                                    <td class="valor-total">$0.00</td>
                                    <td><input type="checkbox" class="checkUnidad"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                        <form action="{{ route('sanciones.truncate') }}" method="POST">
                            @csrf
                            <input type="submit" class="btn btn-danger" value="Limpiar datos" />
                        </form>
                        <button id="btnDetalleUnidad" disabled data-bs-toggle="modal" data-bs-target="#modalDetalle"
                            type="button" class="btn btn-primary">Ver Detalle de la unidad</button>

                        @if (!empty($detalles['ruta']) && $detalles['ruta'] === 'S-N')
                            <button type="button" class="btn btn-success"
                                onclick="location.href='{{ route('sanciones.index', ['parametro' => 'N-S']) }}'">
                                Ver ruta Norte-Sur
                            </button>
                        @endif

                        @if (!empty($detalles['ruta']) && $detalles['ruta'] === 'N-S')
                            <button type="button" class="btn btn-success"
                                onclick="location.href='{{ route('sanciones.index', ['parametro' => 'S-N']) }}'">
                                Ver ruta Sur-Norte
                            </button>
                        @endif


                    </div>
                    <div class="text-center mt-4">
                        <form id="formGenerarReporte" action="{{ route('sanciones.generarReporte') }}" method="POST">
                            @csrf
                            <input type="hidden" name="datosSeleccionados" id="datosSeleccionados">
                            <button type="submit" class="btn btn-success">Generar Reporte Excel</button>
                        </form>
                    </div>
                @endif
            </div>
        </section>
    </main>
    <!-- Modal para mostrar el detalle de una unidad -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl"> <!-- Cambia a modal-xl para mayor tamaño -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalleLabel">Detalle de Unidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <h5 class="text-center mb-3">Sanciones de la unidad seleccionada Ruta: {{ $detalles['ruta'] ?? 'No disponible' }}</h5>
                    
                    <!-- Mostrar el valor de la unidad seleccionada -->
                    <div class="mb-3">
                        <h6><strong>Unidad Seleccionada:</strong> <span id="unidadSeleccionadaTexto"></span></h6>
                    </div>

                    <div class="table-responsive"> <!-- Agregado para hacer la tabla desplazable -->
                        <table class="table table-bordered text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>Vuelta</th>
                                    <th>Geocercas caídas</th>
                                    <th>Total de Sanciones</th>
                                    <th>Valor por geocerca</th>
                                    <th>Valor Total</th>
                                </tr>
                            </thead>
                            <tbody id="detalleUnidadBody">
                                <!-- Detalles llenados dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <input type="hidden" name="unidadSeleccionada" id="unidadSeleccionadaModal">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // 🔥 Verificar si DataTable ya está inicializado antes de reinicializarlo
            if ($.fn.DataTable.isDataTable("#tablaSanciones")) {
                $('#tablaSanciones').DataTable().destroy(); // Destruir DataTable si ya existe
            }

            // Inicializar DataTable correctamente
            let tabla = $('#tablaSanciones').DataTable({
                paging: true,
                searching: true,
                info: false,
                language: {
                    search: "Buscar:",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior",
                    },
                    zeroRecords: "No se encontraron resultados",
                }
            });

            const checkAll = $('#checkAll');

            // ✅ SELECCIONAR TODAS LAS FILAS, INCLUYENDO LAS NO VISIBLES
            checkAll.on('change', function() {
                let isChecked = $(this).prop('checked');

                // Marcar/desmarcar TODAS las filas, incluso las que NO están visibles
                tabla.rows().every(function() {
                    let node = $(this.node());
                    node.find('.checkUnidad').prop('checked', isChecked);
                });

                // 🔥 Asegurar que TODOS los valores de las filas cambien correctamente
                calcularTotales();
            });

            // ✅ SI UN CHECKBOX INDIVIDUAL CAMBIA, ACTUALIZAR EL ESTADO DEL "SELECCIONAR TODOS"
            $('#tablaSanciones tbody').on('change', '.checkUnidad', function() {
                let totalCheckboxes = tabla.$('.checkUnidad').length;
                let checkedCheckboxes = tabla.$('.checkUnidad:checked').length;

                checkAll.prop('checked', totalCheckboxes === checkedCheckboxes);

                // 🔥 Recalcular el valor total individualmente
                calcularTotales();
            });

            // ✅ FUNCIÓN PARA RECALCULAR LOS VALORES TOTALES DE TODAS LAS FILAS
            function calcularTotales() {
                let allRows = tabla.rows().nodes(); // 🔥 Obtener TODAS las filas, visibles o no

                $(allRows).each(function() {
                    let checkbox = $(this).find('.checkUnidad');
                    let totalSancionesCell = $(this).find('.total-sanciones');
                    let valorTotalCell = $(this).find('.valor-total');

                    if (checkbox.prop('checked')) {
                        let totalSanciones = parseInt(totalSancionesCell.text().trim()) || 0;
                        let valorTotal = totalSanciones * 0.50; // Multiplicador por geocerca caída
                        valorTotalCell.text(`$${valorTotal.toFixed(2)}`);
                    } else {
                        valorTotalCell.text('$0.00');
                    }
                });
            }

            // ✅ AL CARGAR LA PÁGINA, HACER QUE LOS VALORES SE ACTUALICEN CORRECTAMENTE
            calcularTotales();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const tabla = $('#tablaSanciones').DataTable({
                paging: true,
                searching: true, // Habilitamos la búsqueda general para aprovechar la funcionalidad
                info: false,
                language: {
                    search: "Buscar:",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior",
                    },
                    zeroRecords: "No se encontraron resultados",
                },
            });

            const filtroUnidad = document.getElementById('filtroUnidad');
            // Escuchar cambios en los checkboxes
            document.querySelector('#tablaSanciones tbody').addEventListener('change', function(e) {
                if (e.target.classList.contains('checkUnidad')) {
                    calcularTotales();
                }
            });

            function calcularTotales() {
                document.querySelectorAll('#tablaSanciones tbody tr').forEach(fila => {
                    const checkbox = fila.querySelector('.checkUnidad');
                    const totalSancionesCell = fila.querySelector('.total-sanciones');
                    const valorTotalCell = fila.querySelector('.valor-total');

                    if (checkbox?.checked) {
                        const totalSanciones = parseInt(totalSancionesCell.textContent.trim());
                        const valorTotal = totalSanciones * 0.50; // Nuevo valor fijo por cada geocerca
                        valorTotalCell.textContent = `$${valorTotal.toFixed(2)}`;
                    } else {
                        valorTotalCell.textContent = '$0.00';
                    }
                });
            }

            // Ejecutar el cálculo inicial para actualizar valores visibles
            calcularTotales();
        });
        document.getElementById('formGenerarReporte').addEventListener('submit', function(e) {
            e.preventDefault(); // Evita el envío automático del formulario

            const tabla = $('#tablaSanciones').DataTable();

            // Obtener los nombres de las geocercas desde el encabezado de la tabla
            const nombresGeocercas = [...document.querySelectorAll('#tablaSanciones thead th')]
                .slice(3, -3) // Ignorar las primeras 3 columnas y las últimas 3 (Total, Valor Total, Seleccionar)
                .map(th => th.textContent.trim());

            // Obtener TODAS las filas de la tabla, no solo las visibles
            const filasSeleccionadas = tabla.rows().nodes().to$().filter((_, fila) => {
                const checkbox = fila.querySelector('.checkUnidad');
                return checkbox && checkbox.checked; // Solo incluir filas con el checkbox marcado
            });

            // Construir los datos seleccionados
            const datosSeleccionados = filasSeleccionadas.map((_, fila) => {
                const $fila = $(fila);

                // Crear un objeto con todas las geocercas y sus valores (llenando con 0 donde no aplica)
                const geocercas = {};
                nombresGeocercas.forEach((nombreGeocerca, index) => {
                    geocercas[nombreGeocerca] = $fila.find(`td:nth-child(${index + 4})`).text()
                        .trim() || '0';
                });

                return {
                    vuelta: $fila.find('td:nth-child(1)').text().trim(),
                    unidad: $fila.find('td:nth-child(2)').text().trim(),
                    hora: $fila.find('td:nth-child(3)').text().trim(),
                    geocercas: geocercas,
                    total: $fila.find('.total-sanciones').text().trim(),
                    valor_total: $fila.find('.valor-total').text()
                        .trim(), // Asegurar captura del valor total
                };
            }).get();

            console.log("Datos enviados al backend:",
                datosSeleccionados); // Debugging para ver si se están enviando correctamente

            if (datosSeleccionados.length === 0) {
                alert('Por favor, selecciona al menos una unidad para generar el reporte.');
                return;
            }

            document.getElementById('datosSeleccionados').value = JSON.stringify(datosSeleccionados);

            // Enviar el formulario
            this.submit();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnDetalleUnidad = document.getElementById('btnDetalleUnidad');
            const detalleUnidadBody = document.getElementById('detalleUnidadBody');
            const unidadSeleccionadaTexto = document.getElementById('unidadSeleccionadaTexto');
            const unidadSeleccionadaModal = document.getElementById('unidadSeleccionadaModal');

            document.querySelector('#tablaSanciones tbody').addEventListener('change', function(e) {
                if (e.target.classList.contains('checkUnidad')) {
                    const checkboxes = document.querySelectorAll('.checkUnidad:checked');

                    if (checkboxes.length > 0) {
                        const unidadesSeleccionadas = [...new Set(
                            Array.from(checkboxes).map(checkbox =>
                                checkbox.closest('tr').querySelector('td:nth-child(2)').textContent
                                .trim()
                            )
                        )];

                        if (unidadesSeleccionadas.length === 1) {
                            const unidad = unidadesSeleccionadas[0];
                            unidadSeleccionadaTexto.textContent = unidad;
                            unidadSeleccionadaModal.value = unidad;
                            btnDetalleUnidad.removeAttribute('disabled');

                            // Calcular el total de sanciones de todas las vueltas de la misma unidad
                            detalleUnidadBody.innerHTML = '';
                            let totalGeneral = 0;

                            checkboxes.forEach(checkbox => {
                                const fila = checkbox.closest('tr');
                                const vuelta = fila.querySelector('td:nth-child(1)').textContent
                                    .trim();
                                const sanciones = fila.querySelectorAll('td');
                                const nombresGeocercas = Array.from(document.querySelectorAll(
                                        '#tablaSanciones thead th'))
                                    .slice(3, -3)
                                    .map(th => th.textContent.trim());

                                let totalSanciones = 0;
                                let geocercasConSanciones = [];

                                nombresGeocercas.forEach((geocerca, index) => {
                                    const valor = sanciones[index + 3].textContent.trim();
                                    if (valor === '1') {
                                        totalSanciones++;
                                        geocercasConSanciones.push(geocerca);
                                    }
                                });

                                // Calcular el valor total por vuelta
                                const valorTotal = totalSanciones *
                                    0.50; // Precio fijo por geocerca
                                totalGeneral += valorTotal;

                                // Renderizar la fila en el modal
                                detalleUnidadBody.innerHTML += `
                            <tr>
                                <td>${vuelta}</td>
                                <td>${geocercasConSanciones.join(', ')}</td>
                                <td>${totalSanciones}</td>
                                <td>$0.50</td>
                                <td>$${valorTotal.toFixed(2)}</td>
                            </tr>`;
                            });

                            // Agregar total general en la última fila del modal
                            detalleUnidadBody.innerHTML += `
                        <tr class="table-dark">
                            <td colspan="4" class="text-end"><strong>Total a pagar:</strong></td>
                            <td><strong>$${totalGeneral.toFixed(2)}</strong></td>
                        </tr>`;
                        } else {
                            btnDetalleUnidad.setAttribute('disabled', 'disabled');
                            unidadSeleccionadaTexto.textContent = '';
                            unidadSeleccionadaModal.value = '';
                        }
                    } else {
                        btnDetalleUnidad.setAttribute('disabled', 'disabled');
                        unidadSeleccionadaTexto.textContent = '';
                        unidadSeleccionadaModal.value = '';
                    }
                }
            });
        });
    </script>
@endsection
