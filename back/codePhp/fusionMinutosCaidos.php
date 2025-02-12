<?php
ini_set('memory_limit', '-1'); // Eliminar límite de memoria
ini_set('max_execution_time', 0); // Eliminar límite de tiempo de ejecución

$token = "795eb63f47c84e37925480c4f5f1ecaf";

date_default_timezone_set('America/Guayaquil');

$url_routes = "https://nimbus.wialon.com/api/depot/9125/routes";
$url_report_base = "https://nimbus.wialon.com/api/depot/9125/report/route/";
$url_stops = "https://nimbus.wialon.com/api/depot/9125/stops";
$url_rides = "https://nimbus.wialon.com/api/depot/9125/rides";

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

if (!$routes_data || !isset($routes_data['routes']))
    die("Error al obtener las rutas");
if (!$stops_data || !isset($stops_data['stops']))
    die("Error al obtener las paradas");
if (!$rides_data || !isset($rides_data['rides']))
    die("Error al obtener los viajes en tiempo real");

$date = date('Y-m-d');
$finished_rides = [];
$routes_map = [];
$stops_map = [];

foreach ($stops_data['stops'] as $stop) {
    $stops_map[$stop['id']] = $stop['n'] ?? "Desconocido";
}

foreach ($routes_data['routes'] as $route) {
    if (!isset($route['tt']))
        continue;
    foreach ($route['tt'] as $timetable) {
        $tid = $timetable['id'];
        $routes_map[$tid] = [
            'route_name' => $route['n'],
            'stops' => array_map(fn($stop) => $stops_map[$stop['id']] ?? "Desconocido", $route['st'] ?? [])
        ];
    }
    $route_id = $route['id'];
    $url_report = "$url_report_base$route_id?flags=1&df=$date&dt=$date&sort=timetable";
    $report_data = getApiData($url_report, $token);
    if ($report_data && isset($report_data['report_data']['rows'])) {
        $finished_rides[$route_id] = $report_data['report_data']['rows'];
    }
}

$grouped_rides = [];
foreach ($rides_data['rides'] as $ride) {
    $tid = $ride['tid'] ?? null;
    if (!$tid || !isset($routes_map[$tid]))
        continue;
    $grouped_rides[$tid][] = $ride;
}

echo "<h2>Resumen de Viajes (Finalizados + Tiempo Real)</h2>";
foreach ($routes_map as $route_id => $route) {
    $route_name = $route['route_name'];
    echo "<h3>Ruta: $route_name</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID Unidad</th>";

    foreach ($route['stops'] as $stop_name) {
        echo "<th colspan='3'>$stop_name</th>";
    }
    echo "</tr><tr><th></th>";

    foreach ($route['stops'] as $stop_name) {
        echo "<th>Plan</th><th>Eje</th><th>Dif</th>";
    }
    echo "</tr>";

    if (isset($finished_rides[$route_id])) {
        foreach ($finished_rides[$route_id] as $unit_data) {
            echo "<tr><td>{$unit_data['cols'][0]['t']} (Finalizado)</td>";
            foreach ($unit_data['rows'] as $stop_data) {
                $plan_time = $stop_data[3]['t'] ?? "--:--";
                $exec_time = $stop_data[4]['t'] ?? "--:--";
                $diff = ($exec_time && $plan_time) ? strtotime($exec_time) - strtotime($plan_time) : "-";
                echo "<td>$plan_time</td><td>$exec_time</td><td>$diff</td>";
            }
            echo "</tr>";
        }
    }

    if (isset($grouped_rides[$route_id])) {
        foreach ($grouped_rides[$route_id] as $ride) {
            echo "<tr style='background-color:#f0f8ff'><td>{$ride['u']} (Tiempo Real)</td>";
            foreach ($route['stops'] as $index => $stop_name) {
                $plan_time = isset($ride['pt'][$index]) ? date('H:i', $ride['pt'][$index]) : "--:--";
                $exec_time = isset($ride['at'][$index]) ? date('H:i', $ride['at'][$index]) : "--:--";
                $diff = ($exec_time && $plan_time) ? strtotime($exec_time) - strtotime($plan_time) : "-";
                echo "<td>$plan_time</td><td>$exec_time</td><td>$diff</td>";
            }
            echo "</tr>";
        }
    }
    echo "</table><br>";
}
?>