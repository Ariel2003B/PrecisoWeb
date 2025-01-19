@extends('layout')

@section('Titulo', 'Sanciones')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <section class="container mt-5">

        <h1 class="text-center mb-4">Reporte de Sanciones</h1>

        {{-- Formulario para cargar el archivo --}}
        <form action="{{ route('sanciones.cargarCSV') }}" method="POST" enctype="multipart/form-data" class="mb-4">
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
                <thead class="table-dark sticky-top">
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
                        <th class="geocerca">Seleccionar</th>
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
            </div>
            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                <form action="{{ route('sanciones.truncate') }}" method="POST">
                    @csrf
                    <input type="submit" class="btn btn-danger" value="Limpiar datos" />
                </form>
                <button id="btnDetalleUnidad" disabled data-bs-toggle="modal" data-bs-target="#modalDetalle" type="button"
                    class="btn btn-contador">Ver Detalle de la unidad</button>

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
            {{-- <div class="text-center mt-4">
                <button  class="btn btn-primary" >
                    Ver Detalle de Unidad
                </button>
            </div> --}}
        @endif

    </section>

    <!-- Modal para mostrar el detalle de una unidad -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalleLabel">Detalle de Unidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <h5 class="text-center mb-3">Sanciones de la unidad seleccionada</h5>

                    <!-- Mostrar el valor de la unidad seleccionada -->
                    <div class="mb-3">
                        <h6><strong>Unidad Seleccionada:</strong> <span id="unidadSeleccionadaTexto"></span></h6>
                    </div>

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
                <input type="hidden" name="unidadSeleccionada" id="unidadSeleccionadaModal">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    {{-- <script>
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

            // Filtro manual por unidad
            filtroUnidad.addEventListener('input', function() {
                const unidad = filtroUnidad.value.trim();
                tabla.column(1).search(unidad, false, false)
                    .draw(); // Filtrar por la columna de la unidad (índice 1)
            });

            // Escuchar cambios en los checkboxes
            document.querySelector('#tablaSanciones tbody').addEventListener('change', function(e) {
                if (e.target.classList.contains('checkUnidad')) {
                    calcularTotales();
                }
            });

            function calcularTotales() {
                const unidadesReincidencia = {};

                // Iterar sobre las filas y calcular los valores
                document.querySelectorAll('#tablaSanciones tbody tr').forEach(fila => {
                    const checkbox = fila.querySelector('.checkUnidad');
                    const unidad = fila.querySelector('td:nth-child(2)').textContent.trim();
                    const totalSancionesCell = fila.querySelector('.total-sanciones');
                    const valorTotalCell = fila.querySelector('.valor-total');

                    if (!unidadesReincidencia[unidad]) unidadesReincidencia[unidad] = 0;

                    if (checkbox?.checked) {
                        unidadesReincidencia[unidad]++;
                        const totalSanciones = parseInt(totalSancionesCell.textContent.trim());
                        const valorTotal = totalSanciones * (0.25 * unidadesReincidencia[unidad]);
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
            e.preventDefault(); // Detener el envío automático

            const tabla = $('#tablaSanciones').DataTable();

            // Obtener los nombres de las geocercas desde el encabezado de la tabla
            const nombresGeocercas = [...document.querySelectorAll('#tablaSanciones thead th')]
                .slice(3, -3) // Ignorar las primeras 3 columnas y las últimas 3 (Total, Valor Total, Seleccionar)
                .map(th => th.textContent.trim());

            // Obtener todas las filas seleccionadas (visible o no visible)
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
                    unidad: $fila.find('td:nth-child(2)').text().trim(),
                    placa: $fila.find('td:nth-child(3)').text().trim(),
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

            // Enviar el formulario
            this.submit();
        });
    </script> --}}
    <script>
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
                const unidadesReincidencia = {};

                // Iterar sobre las filas y calcular los valores
                document.querySelectorAll('#tablaSanciones tbody tr').forEach(fila => {
                    const checkbox = fila.querySelector('.checkUnidad');
                    const unidad = fila.querySelector('td:nth-child(2)').textContent.trim();
                    const totalSancionesCell = fila.querySelector('.total-sanciones');
                    const valorTotalCell = fila.querySelector('.valor-total');

                    if (!unidadesReincidencia[unidad]) unidadesReincidencia[unidad] = 0;

                    if (checkbox?.checked) {
                        unidadesReincidencia[unidad]++;
                        const totalSanciones = parseInt(totalSancionesCell.textContent.trim());
                        const valorTotal = totalSanciones * (0.25 * unidadesReincidencia[unidad]);
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
            e.preventDefault(); // Detener el envío automático

            const tabla = $('#tablaSanciones').DataTable();

            // Obtener los nombres de las geocercas desde el encabezado de la tabla
            const nombresGeocercas = [...document.querySelectorAll('#tablaSanciones thead th')]
                .slice(3, -3) // Ignorar las primeras 3 columnas y las últimas 3 (Total, Valor Total, Seleccionar)
                .map(th => th.textContent.trim());

            // Obtener todas las filas seleccionadas (visible o no visible)
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
                    vuelta: $fila.find('td:nth-child(1)').text()
                        .trim(), // Capturar el valor de la columna vuelta
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

            // Enviar el formulario
            this.submit();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM cargado correctamente');

            const btnDetalleUnidad = document.getElementById('btnDetalleUnidad');
            const tablaBody = document.querySelector('#tablaSanciones tbody');
            const detalleUnidadBody = document.getElementById('detalleUnidadBody');
            const unidadSeleccionadaModal = document.getElementById('unidadSeleccionadaModal');
            const unidadSeleccionadaTexto = document.getElementById('unidadSeleccionadaTexto');

            if (!tablaBody || !btnDetalleUnidad || !detalleUnidadBody || !unidadSeleccionadaModal || !
                unidadSeleccionadaTexto) {
                console.error('No se encontraron elementos necesarios en el DOM');
                return;
            }

            console.log('Añadiendo eventos');

            // Escuchar cambios en los checkboxes
            tablaBody.addEventListener('change', function(e) {
                if (e.target.classList.contains('checkUnidad')) {
                    console.log('Checkbox detectado:', e.target);

                    const checkboxes = document.querySelectorAll('.checkUnidad');
                    const checkedBoxes = Array.from(checkboxes).filter(checkbox => checkbox.checked);

                    if (checkedBoxes.length > 0) {
                        // Verificar si todas las unidades seleccionadas son iguales
                        const unidadesSeleccionadas = checkedBoxes.map(checkbox => {
                            const fila = checkbox.closest('tr');
                            return fila.querySelector('td:nth-child(2)').textContent.trim();
                        });

                        const unidadUnica = [...new Set(
                            unidadesSeleccionadas)]; // Lista única de unidades seleccionadas

                        if (unidadUnica.length === 1) {
                            console.log('Todas las unidades seleccionadas son iguales:', unidadUnica[0]);

                            // Mostrar la unidad seleccionada en el modal
                            unidadSeleccionadaTexto.textContent = unidadUnica[0];
                            unidadSeleccionadaModal.value = unidadUnica[0];

                            // Habilitar el botón
                            btnDetalleUnidad.removeAttribute('disabled');

                            // Llenar el modal con detalles
                            detalleUnidadBody.innerHTML = '';
                            let totalGeneral = 0;

                            // Calcular valor basado en la vuelta
                            let vueltaReincidencia = 0;

                            checkedBoxes.forEach(checkbox => {
                                const fila = checkbox.closest('tr');
                                const vuelta = fila.querySelector('td:nth-child(1)').textContent
                                    .trim();
                                const sanciones = fila.querySelectorAll('td');
                                const nombresGeocercas = Array.from(document.querySelectorAll(
                                        '#tablaSanciones thead th'))
                                    .slice(3, -3) // Ignorar columnas irrelevantes
                                    .map(th => th.textContent.trim());

                                vueltaReincidencia++; // Incrementar la reincidencia por vuelta seleccionada
                                const valorBase = 0.25 *
                                    vueltaReincidencia; // Calcular el valor base según la vuelta

                                let totalSanciones = 0;
                                let valorTotal = 0.0;
                                let geocercasConSanciones = [];

                                nombresGeocercas.forEach((geocerca, index) => {
                                    const valor = sanciones[index + 3].textContent.trim();

                                    if (valor === '1') {
                                        totalSanciones++;
                                        geocercasConSanciones.push(`${geocerca}`);
                                    }
                                });

                                // Calcular el valor total de la vuelta
                                valorTotal = totalSanciones * valorBase;
                                totalGeneral += valorTotal;

                                // Renderizar la fila para esta vuelta
                                detalleUnidadBody.innerHTML += `
                        <tr>
                            <td>${vuelta}</td>
                            <td>${geocercasConSanciones.join(', ')}</td>
                            <td>${totalSanciones}</td>
                            <td>$${valorBase.toFixed(2)}</td>
                            <td>$${valorTotal.toFixed(2)}</td>
                        </tr>`;
                            });

                            // Agregar la fila final para el total general
                            detalleUnidadBody.innerHTML += `
                    <tr class="table-dark">
                        <td colspan="4" class="text-end"><strong>Total a pagar:</strong></td>
                        <td><strong>$${totalGeneral.toFixed(2)}</strong></td>
                    </tr>`;
                        } else {
                            console.log('Se seleccionaron unidades diferentes');
                            btnDetalleUnidad.setAttribute('disabled', 'disabled');
                            unidadSeleccionadaTexto.textContent = '';
                            unidadSeleccionadaModal.value = '';
                        }
                    } else {
                        console.log('No hay checkboxes seleccionados');
                        btnDetalleUnidad.setAttribute('disabled', 'disabled');
                        unidadSeleccionadaTexto.textContent = '';
                        unidadSeleccionadaModal.value = '';
                    }
                }
            });
        });
    </script>
@endsection


@section('jsCode', 'js/scriptNavBar.js')
