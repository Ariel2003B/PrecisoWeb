<?php
$token = "795eb63f47c84e37925480c4f5f1ecaf";

// Zona horaria de Quito (GMT-5)
date_default_timezone_set('America/Guayaquil');

// URLs de los endpoints
$url_routes = "https://nimbus.wialon.com/api/depot/9125/routes";
$url_report_base = "https://nimbus.wialon.com/api/depot/9125/report/route/";

// urls para lo segundo 
$url_stops = "https://nimbus.wialon.com/api/depot/9125/stops";
$url_rides = "https://nimbus.wialon.com/api/depot/9125/rides";

// Función para obtener datos de la API
function getApiData($url, $token)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "Authorization: Token $token"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}


$routes_data = getApiData($url_routes, $token);
$stops_data = getApiData($url_stops, $token);
$rides_data = getApiData($url_rides, $token);

if (!$routes_data || !isset($routes_data['routes'])) {
    die("Error al obtener las rutas");
}
if (!$stops_data || !isset($stops_data['stops'])) {
    die("Error al obtener las paradas");
}
if (!$rides_data || !isset($rides_data['rides'])) {
    die("Error al obtener los viajes");
}

// Función para convertir HH:MM o HH:MM:SS a segundos
function timeToSeconds($time)
{
    if (empty($time) || !is_string($time) || strpos($time, ":") === false) {
        return null; // Retornar null si el valor no es válido
    }

    $parts = explode(":", $time);

    // Si solo hay horas y minutos, agregar segundos como 00
    if (count($parts) == 2) {
        $parts[] = "00";
    }

    if (count($parts) < 3) {
        return null; // Evitar errores si el formato no es HH:MM:SS
    }

    list($h, $m, $s) = $parts;

    if (!is_numeric($h) || !is_numeric($m) || !is_numeric($s)) {
        return null; // Evitar errores si los valores no son números
    }

    return ($h * 3600) + ($m * 60) + $s;
}

// Obtener lista de rutas disponibles
$routes_data = getApiData($url_routes, $token);
if (!$routes_data || !isset($routes_data['routes'])) {
    die("Error al obtener las rutas");
}

// Fecha para el reporte
$date = date('Y-m-d');



// Procesar cada ruta
foreach ($routes_data['routes'] as $route) {
    $route_id = $route['id'];
    $route_name = $route['n'];
    $url_report = "$url_report_base$route_id?flags=1&df=\"$date\"&dt=\"$date\"&sort=timetable";
    $report_data = getApiData($url_report, $token);

    if (!$report_data || !isset($report_data['report_data']['rows'])) {
        echo "<h3>Error al obtener datos de la ruta: $route_name</h3>";
        continue;
    }

    echo "<h3>Ruta: $route_name</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID Unidad</th>";

    // Extraer nombres de paradas desde los datos de la primera unidad
    $stops = [];
    foreach ($report_data['report_data']['rows'][0]['rows'] as $stop_data) {
        if (isset($stop_data[0]['t'])) {
            $stops[] = $stop_data[0]['t'];
            echo "<th colspan='3'>{$stop_data[0]['t']}</th>";
        }
    }
    echo "</tr>";

    // Sub-encabezado
    echo "<tr><th></th>";
    foreach ($stops as $stop) {
        echo "<th>Plan</th><th>Eje</th><th>Dif</th>";
    }
    echo "</tr>";

    // Datos de las unidades en la ruta
    foreach ($report_data['report_data']['rows'] as $unit_data) {
        $unit_name = $unit_data['cols'][0]['t'];
        echo "<tr><td>$unit_name</td>";

        foreach ($unit_data['rows'] as $stop_data) {
            $plan_time = isset($stop_data[3]['t']) ? $stop_data[3]['t'] : "--:--";
            $exec_time = isset($stop_data[4]['t']) ? $stop_data[4]['t'] : "--:--";

            // Convertir a segundos
            $plan_seconds = timeToSeconds($plan_time);
            $exec_seconds = timeToSeconds($exec_time);

            // Calcular la diferencia en minutos sin redondeo
            if (!is_null($plan_seconds) && !is_null($exec_seconds)) {
                $diff_seconds = $exec_seconds - $plan_seconds;
                $diff_minutes = floor($diff_seconds / 60); // Minutos completos

                // Invertir signo de la diferencia
                $diff_display = ($diff_seconds == 0) ? "0" : ($diff_minutes * -1);
            } else {
                $diff_display = "-";
            }
            echo "<td>$plan_time</td><td>$exec_time</td><td>$diff_display</td>";
        }
        echo "</tr>";
    }




    echo "</table><br>";
}


// Crear un mapa de paradas por ID para acceso rápido
$stops_map = [];
foreach ($stops_data['stops'] as $stop) {
    $stops_map[$stop['id']] = $stop['n'];
}

// Crear un mapa de rutas por tid (timetable ID)
$routes_map = [];
foreach ($routes_data['routes'] as $route) {
    if (!isset($route['tt']))
        continue;
    foreach ($route['tt'] as $timetable) {
        $tid = $timetable['id'];
        $routes_map[$tid] = [
            'route_name' => $route['n'],
            'stops' => $route['st'] ?? []
        ];
    }
}
// Agrupar viajes por ruta
$grouped_rides = [];
foreach ($rides_data['rides'] as $ride) {
    $tid = $ride['tid'] ?? null;
    if (!$tid || !isset($routes_map[$tid]))
        continue;

    $route_name = $routes_map[$tid]['route_name'];
    if (!isset($grouped_rides[$route_name])) {
        $grouped_rides[$route_name] = [];
    }
    $grouped_rides[$route_name][] = $ride;
}

// Generar una tabla por cada ruta
echo "<h2>Horarios de Rutas</h2>";
foreach ($grouped_rides as $route_name => $rides) {
    echo "<h3>Ruta: $route_name</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID Unidad</th>";

    // Encabezado con nombres de paradas correspondientes a esta ruta
    $tid = $rides[0]['tid'];
    foreach ($routes_map[$tid]['stops'] as $stop) {
        if (isset($stops_map[$stop['id']])) {
            echo "<th colspan='3'>{$stops_map[$stop['id']]}</th>";
        }
    }
    echo "</tr>";

    // Sub-encabezado
    echo "<tr><th></th>";
    foreach ($routes_map[$tid]['stops'] as $stop) {
        if (isset($stops_map[$stop['id']])) {
            echo "<th>Plan</th><th>Eje</th><th>Dif</th>";
        }
    }
    echo "</tr>";

    // Datos de los viajes
    foreach ($rides as $ride) {
        $unit_id = $ride['u'] ?? "Desconocido";
        echo "<tr><td>$unit_id</td>";

        foreach ($routes_map[$tid]['stops'] as $index => $stop) {
            if (!isset($stops_map[$stop['id']]))
                continue;

            $plan_time = isset($ride['pt'][$index]) ? date('H:i', $ride['pt'][$index]) : "--:--";
            $exec_time = isset($ride['at'][$index]) && $ride['at'][$index] ? date('H:i', $ride['at'][$index]) : "--:--";

            $diff = "-";
            if (isset($ride['pt'][$index]) && isset($ride['at'][$index]) && $ride['at'][$index]) {
                $plan_min = intval(date('i', $ride['pt'][$index]));
                $exec_min = intval(date('i', $ride['at'][$index]));
                $diff = $exec_min - $plan_min;
                $diff = ($diff >= 0 && $diff < 1) ? 0 : ($diff > 0 ? -$diff : abs($diff));
            }

            echo "<td>$plan_time</td><td>$exec_time</td><td>$diff</td>";
        }
        echo "</tr>";
    }
    echo "</table><br>";
}
?>