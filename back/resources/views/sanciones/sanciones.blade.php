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
                                @foreach ($geocercas as $index => $geocerca)
                                    <th class="geocerca">
                                        <input type="checkbox" class="checkGeocerca" data-index="{{ $index }}"
                                            checked>
                                        {{ $geocerca }}
                                    </th>
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
                            <input type="hidden" name="geocercasActivas" id="geocercasActivas">
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
                    <h5 class="text-center mb-3">Sanciones de la unidad seleccionada Ruta:
                        {{ $detalles['ruta'] ?? 'No disponible' }}</h5>

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
        let columnasExcluidas = [];

        $(document).ready(function() {
            let tabla;
            if (!$.fn.DataTable.isDataTable('#tablaSanciones')) {
                tabla = $('#tablaSanciones').DataTable({
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
            } else {
                tabla = $('#tablaSanciones').DataTable();
            }


            const checkAll = $('#checkAll');

            checkAll.on('change', function() {
                let isChecked = $(this).prop('checked');

                // 🔥 Seleccionar TODAS LAS FILAS de TODAS LAS PÁGINAS
                tabla.rows().every(function() {
                    $(this.node()).find('.checkUnidad').prop('checked', isChecked);
                });

                calcularTotales();
            });


            // ✅ Seleccionar fila individual
            $('#tablaSanciones tbody').on('change', '.checkUnidad', function() {
                calcularTotales();
            });

            // ✅ Cambiar checkbox de geocercas
            document.querySelectorAll('.checkGeocerca').forEach(check => {
                check.addEventListener('change', function() {
                    const index = parseInt(this.dataset.index);
                    if (this.checked) {
                        columnasExcluidas = columnasExcluidas.filter(i => i !== index);
                    } else {
                        if (!columnasExcluidas.includes(index)) {
                            columnasExcluidas.push(index);
                        }
                    }
                    calcularTotales();
                });
            });

            // ✅ Función para calcular el total y valor total
            function calcularTotales() {
                tabla.rows().every(function() {
                    const fila = $(this.node());
                    const checkbox = fila.find('.checkUnidad').prop('checked');
                    let totalSanciones = 0;

                    // Recorremos todas las celdas de geocercas (asumiendo que son desde la columna 4 en adelante)
                    fila.find('td').each(function(colIndex) {
                        const columnaGeocerca = colIndex -
                            3; // Ajustamos según las primeras 3 columnas
                        const valorCelda = $(this).text().trim();

                        if (columnaGeocerca >= 0 && valorCelda === '1' && !columnasExcluidas
                            .includes(columnaGeocerca)) {
                            totalSanciones++;
                        }
                    });

                    fila.find('.total-sanciones').text(totalSanciones);
                    const valorTotal = totalSanciones * 0.50;
                    fila.find('.valor-total').text(checkbox ? `$${valorTotal.toFixed(2)}` : '$0.00');
                });
            }
            // ✅ Ejecutar al inicio
            calcularTotales();
        });
        document.getElementById('formGenerarReporte').addEventListener('submit', function(e) {
            e.preventDefault();

            const tabla = $('#tablaSanciones').DataTable();

            // Obtener los nombres de las geocercas desde el encabezado de la tabla
            const nombresGeocercas = [...document.querySelectorAll('#tablaSanciones thead th')]
                .slice(3, -3)
                .map(th => th.textContent.trim());

            // Obtener las geocercas activas (no excluidas)
            const geocercasActivas = nombresGeocercas.filter((_, index) => !columnasExcluidas.includes(index));

            const filasSeleccionadas = tabla.rows().nodes().to$().filter((_, fila) => {
                const checkbox = fila.querySelector('.checkUnidad');
                return checkbox && checkbox.checked;
            });

            const datosSeleccionados = filasSeleccionadas.map((_, fila) => {
                const $fila = $(fila);

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
                    valor_total: $fila.find('.valor-total').text().trim(),
                };
            }).get();

            if (datosSeleccionados.length === 0) {
                alert('Por favor, selecciona al menos una unidad para generar el reporte.');
                return;
            }

            document.getElementById('datosSeleccionados').value = JSON.stringify(datosSeleccionados);
            document.getElementById('geocercasActivas').value = JSON.stringify(geocercasActivas);

            this.submit();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnDetalleUnidad = document.getElementById('btnDetalleUnidad');
            const detalleUnidadBody = document.getElementById('detalleUnidadBody');
            const unidadSeleccionadaTexto = document.getElementById('unidadSeleccionadaTexto');
            const unidadSeleccionadaModal = document.getElementById('unidadSeleccionadaModal');
            const checkAll = document.getElementById('checkAll');

            let tabla;
            if (!$.fn.DataTable.isDataTable('#tablaSanciones')) {
                tabla = $('#tablaSanciones').DataTable();
            } else {
                tabla = $('#tablaSanciones').DataTable();
            }


            function validarBotonDetalleUnidad() {
                let unidadesSeleccionadas = new Set();

                // 🔥 Revisamos TODAS las páginas, no solo la visible
                tabla.rows().every(function() {
                    const fila = $(this.node());
                    if (fila.find('.checkUnidad').prop('checked')) {
                        const unidad = fila.find('td:nth-child(2)').text().trim();
                        unidadesSeleccionadas.add(unidad);
                    }
                });

                if (unidadesSeleccionadas.size === 1) {
                    btnDetalleUnidad.removeAttribute('disabled');
                    const unidad = [...unidadesSeleccionadas][0];
                    unidadSeleccionadaTexto.textContent = unidad;
                    unidadSeleccionadaModal.value = unidad;
                } else {
                    btnDetalleUnidad.setAttribute('disabled', 'disabled');
                    unidadSeleccionadaTexto.textContent = '';
                    unidadSeleccionadaModal.value = '';
                }
            }

            function actualizarSeleccionGlobal(isChecked) {
                // ✅ Seleccionamos TODAS las filas, incluyendo las no visibles
                tabla.rows().every(function() {
                    $(this.node()).find('.checkUnidad').prop('checked', isChecked);
                });

                validarBotonDetalleUnidad();
            }

            function calcularTotales() {
                tabla.rows().every(function() {
                    const fila = $(this.node());
                    const checkbox = fila.find('.checkUnidad').prop('checked');
                    let totalSanciones = 0;

                    fila.find('td').each(function(colIndex) {
                        const columnaGeocerca = colIndex - 3;
                        const valorCelda = $(this).text().trim();

                        if (columnaGeocerca >= 0 && valorCelda === '1' && !columnasExcluidas
                            .includes(columnaGeocerca)) {
                            totalSanciones++;
                        }
                    });

                    fila.find('.total-sanciones').text(totalSanciones);
                    const valorTotal = totalSanciones * 0.50;
                    fila.find('.valor-total').text(checkbox ? `$${valorTotal.toFixed(2)}` : '$0.00');
                });
            }

            // ✅ Cuando se cambia el checkbox de "Seleccionar Todos"
            checkAll.addEventListener('change', function() {
                actualizarSeleccionGlobal(this.checked);
                calcularTotales();
            });

            // ✅ Cuando se selecciona/desselecciona una fila individual
            $('#tablaSanciones tbody').on('change', '.checkUnidad', function() {
                validarBotonDetalleUnidad();
                calcularTotales();
            });

            // ✅ Capturar cambios en los checkboxes de geocercas
            document.querySelectorAll('.checkGeocerca').forEach(check => {
                check.addEventListener('change', function() {
                    const index = parseInt(this.dataset.index);
                    if (this.checked) {
                        columnasExcluidas = columnasExcluidas.filter(i => i !== index);
                    } else {
                        if (!columnasExcluidas.includes(index)) {
                            columnasExcluidas.push(index);
                        }
                    }
                    calcularTotales();
                });
            });

            // ✅ Evento para llenar el modal cuando se hace clic en "Ver Detalle de la Unidad"
            btnDetalleUnidad.addEventListener('click', function() {
                detalleUnidadBody.innerHTML = '';
                let totalGeneral = 0;

                const nombresGeocercas = Array.from(document.querySelectorAll('#tablaSanciones thead th'))
                    .slice(3, -3)
                    .map(th => th.textContent.trim());

                tabla.rows().every(function() {
                    const fila = $(this.node());
                    if (fila.find('.checkUnidad').prop('checked')) {
                        const vuelta = fila.find('td:nth-child(1)').text().trim();
                        const sanciones = fila.find('td');

                        let totalSanciones = 0;
                        let geocercasConSanciones = [];

                        sanciones.each(function(colIndex) {
                            const columnaGeocerca = colIndex - 3;
                            const valorCelda = $(this).text().trim();

                            if (columnaGeocerca >= 0 && valorCelda === '1' && !
                                columnasExcluidas.includes(columnaGeocerca)) {
                                totalSanciones++;
                                geocercasConSanciones.push(nombresGeocercas[
                                    columnaGeocerca]);
                            }
                        });

                        const valorTotal = totalSanciones * 0.50;
                        totalGeneral += valorTotal;

                        detalleUnidadBody.innerHTML += `
                    <tr>
                        <td>${vuelta}</td>
                        <td>${geocercasConSanciones.join(', ')}</td>
                        <td>${totalSanciones}</td>
                        <td>$0.50</td>
                        <td>$${valorTotal.toFixed(2)}</td>
                    </tr>
                `;
                    }
                });

                detalleUnidadBody.innerHTML += `
            <tr class="table-dark">
                <td colspan="4" class="text-end"><strong>Total a pagar:</strong></td>
                <td><strong>$${totalGeneral.toFixed(2)}</strong></td>
            </tr>
        `;
            });

            // ✅ Ejecutar validación inicial
            validarBotonDetalleUnidad();
            calcularTotales();
        });
    </script>
@endsection
