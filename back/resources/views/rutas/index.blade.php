@extends('layout')

@section('Titulo', 'Reporte de Minutos Caídos')

@section('content')

    <h1 class="mb-4 text-center">Reporte de Minutos Caídos</h1>

    <!-- Botones para cambiar de ruta -->
    <div class="rutas-buttons">
        @foreach ($grouped_rides as $route_name => $rides)
            <button class="btn-ruta" data-route="{{ str_replace(' ', '_', $route_name) }}">
                {{ $route_name }}
            </button>
        @endforeach
    </div>

    <!-- Contenedor de tablas de rutas -->
    @foreach ($grouped_rides as $route_name => $rides)
        <div class="table-container" id="table-{{ str_replace(' ', '_', $route_name) }}" style="display: none;">
            <h3 class="text-center">Ruta: {{ $route_name }}</h3>
            <div class="scrollable-table">
                <table class="table-rutas">
                    <thead>
                        <tr>
                            <th class="sticky-header sticky-col" rowspan="2">Unidad</th> <!-- Celda fija -->
                            <th class="sticky-header sticky-col-rutina" rowspan="2">Rutina</th> <!-- Celda fija -->
                            @foreach ($routes_map[$rides[0]['tid']]['stops'] as $stop)
                                @if (isset($stops_map[$stop['id']]))
                                    <th colspan="3">{{ $stops_map[$stop['id']] }}</th>
                                @endif
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($routes_map[$rides[0]['tid']]['stops'] as $stop)
                                @if (isset($stops_map[$stop['id']]))
                                    <th class="col-peq">Plan</th>
                                    <th class="col-peq">Eje</th>
                                    <th class="col-peq">Dif</th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rides as $ride)
                            <tr>
                                <td class="sticky-col">{{ $ride['unit_name'] }}</td> <!-- Unidad fija correctamente -->

                                <!-- 📌 Columna Rutina -->
                                @php
                                    $firstStopIndex = array_key_first($ride['pt']);
                                    $lastStopIndex = array_key_last($ride['pt']);

                                    // Obtener la hora planificada de la primera y última parada
                                    $firstPlanTime = isset($ride['pt'][$firstStopIndex])
                                        ? date('H:i', $ride['pt'][$firstStopIndex] - 18000)
                                        : '--:--';

                                    $lastPlanTime = isset($ride['pt'][$lastStopIndex])
                                        ? date('H:i', $ride['pt'][$lastStopIndex] - 18000)
                                        : '--:--';
                                @endphp

                                <td class="sticky-col-rutina">{{ $firstPlanTime }} - {{ $lastPlanTime }}</td>
                                <!-- Rutina -->

                                @foreach ($routes_map[$ride['tid']]['stops'] as $index => $stop)
                                    @if (!isset($stops_map[$stop['id']]))
                                        @continue
                                    @endif

                                    @php
                                        // Ajuste a la zona horaria de Quito (GMT-5)
                                        $plan_time = isset($ride['pt'][$index])
                                            ? date('H:i', $ride['pt'][$index] - 18000)
                                            : '--:--';

                                        $exec_time =
                                            isset($ride['at'][$index]) && $ride['at'][$index]
                                                ? date('H:i', $ride['at'][$index] - 18000)
                                                : '--:--';

                                        // Calcular la diferencia en minutos
                                        $diff = '-';
                                        if (
                                            isset($ride['pt'][$index]) &&
                                            isset($ride['at'][$index]) &&
                                            $ride['at'][$index]
                                        ) {
                                            $plan_min = intval(date('i', $ride['pt'][$index]));
                                            $exec_min = intval(date('i', $ride['at'][$index]));
                                            $diff = $exec_min - $plan_min;

                                            // Aplicar reglas de diferencia
                                            $diff = $diff >= 0 && $diff < 1 ? 0 : ($diff > 0 ? -$diff : abs($diff));
                                        }
                                    @endphp

                                    <td class="col-peq">{{ $plan_time }}</td>
                                    <td class="col-peq">{{ $exec_time }}</td>
                                    <td class="col-peq {{ $diff < 0 ? 'negative' : ($diff > 0 ? 'positive' : '') }}">
                                        {{ $diff }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    @endforeach
    <script>
        function actualizarTabla() {
            fetch("{{ route('rutas.actualizar') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        document.getElementById("tabla-container").innerHTML = data.html;
                    }
                })
                .catch(error => console.error("Error al actualizar la tabla:", error));
        }
        // Actualizar cada minuto
        setInterval(actualizarTabla, 60000);

        // Cargar la tabla al iniciar
        actualizarTabla();
        console.log('actualizado');
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const buttons = document.querySelectorAll(".btn-ruta");
            const tables = document.querySelectorAll(".table-container");

            buttons.forEach(button => {
                button.addEventListener("click", function() {
                    let selectedRoute = this.getAttribute("data-route");
                    let selectedTable = document.getElementById("table-" + selectedRoute);

                    // 🔹 Verifica si el contenedor existe antes de intentar modificarlo
                    if (!selectedTable) {
                        console.error("⚠️ Error: No se encontró la tabla para la ruta:",
                            selectedRoute);
                        return;
                    }

                    // Ocultar todas las tablas
                    tables.forEach(table => {
                        table.style.display = "none";
                    });

                    // Mostrar la tabla de la ruta seleccionada
                    selectedTable.style.display = "block";

                    // Remover la clase "active" de todos los botones
                    buttons.forEach(btn => btn.classList.remove("active"));

                    // Agregar la clase "active" al botón seleccionado
                    this.classList.add("active");
                });
            });

            // Mostrar la primera ruta al cargar y activar el primer botón
            if (buttons.length > 0) {
                buttons[0].click();
            }

            // 🔄 Auto-actualización cada 1 minuto
            setInterval(() => {
                actualizarDatos();
            }, 60000); // 60 segundos
        });

        async function actualizarDatos() {
            try {
                const response = await fetch(window.location.href);
                const html = await response.text();

                // Reemplazar solo la parte de la tabla
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                const newTableContainers = doc.querySelectorAll(".table-container");

                if (!newTableContainers.length) {
                    console.error("⚠️ No se encontraron tablas en la respuesta.");
                    return;
                }

                newTableContainers.forEach(newTable => {
                    let oldTable = document.getElementById(newTable.id);

                    // ✅ Solo actualizar si la tabla existe
                    if (oldTable) {
                        oldTable.innerHTML = newTable.innerHTML;
                    } else {
                        console.warn(`⚠️ La tabla con ID "${newTable.id}" no existe en el DOM.`);
                    }
                });

                console.log("✅ Datos actualizados correctamente.");
            } catch (error) {
                console.error("❌ Error al actualizar la tabla:", error);
            }
        }
    </script>

@endsection
