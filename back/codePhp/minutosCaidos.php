<?php
// Token de autenticación
//$token = "7e88dda43463415cb00bef30cdb3546a";
$token = "fc619d1f1c1549069a09aede082acd1c";

// Zona horaria de Quito (GMT-5)
date_default_timezone_set('America/Guayaquil');

// URLs de los endpoints
$url_routes = "https://nimbus.wialon.com/api/depot/8994/routes";
$url_stops = "https://nimbus.wialon.com/api/depot/8994/stops";
$url_rides = "https://nimbus.wialon.com/api/depot/8994/rides";

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

// Obtener datos de rutas, paradas y viajes
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