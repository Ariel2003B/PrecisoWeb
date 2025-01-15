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
            {{-- Filtro por Unidad --}}
            <div class="mb-4">
                <label for="filtroUnidad" class="form-label">Filtrar por Unidad:</label>
                <input type="text" id="filtroUnidad" class="form-control" placeholder="Ingrese el número de unidad">
            </div>

            {{-- Tabla de sanciones procesadas --}}
            <div class="table-responsive mt-5">
                <h3 class="text-center mb-4">Sanciones Procesadas</h3>
                <table id="tablaSanciones" class="table table-striped table-bordered text-center align-middle">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th class="geocerca">Vuelta</th>
                            <th class="geocerca">Unidad</th>
                            <th class="geocerca">Placa</th>
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
                                <td>{{ $dato['placa'] }}</td>
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
            <div class="text-center mt-4">
                <form id="formGenerarReporte" action="{{ route('sanciones.generarReporte') }}" method="POST">
                    @csrf
                    <input type="hidden" name="datosSeleccionados" id="datosSeleccionados">
                    <button type="submit" class="btn btn-success">Generar Reporte Excel</button>
                </form>
            </div>
        @endif
    </section>
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
                    vuelta: $fila.find('td:nth-child(1)').text()
                .trim(), // Capturar el valor de la columna vuelta
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
    </script>
@endsection


@section('jsCode', 'js/scriptNavBar.js')
