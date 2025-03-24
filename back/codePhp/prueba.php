<?php
$token = "fc619d1f1c1549069a09aede082acd1c";

// Zona horaria de Quito (GMT-5)
date_default_timezone_set('America/Guayaquil');

// URLs de los endpoints
$url_routes = "https://nimbus.wialon.com/api/depot/8994/routes";
$url_report_base = "https://nimbus.wialon.com/api/depot/8994/report/route/";
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

// Obtener datos
$routes_data = getApiData($url_routes, $token);
$stops_data = getApiData($url_stops, $token);
$rides_data = getApiData($url_rides, $token);
$date = date('Y-m-d');

if (!$routes_data || !isset($routes_data['routes'])) {
    die("Error al obtener las rutas");
}
if (!$stops_data || !isset($stops_data['stops'])) {
    die("Error al obtener las paradas");
}
if (!$rides_data || !isset($rides_data['rides'])) {
    die("Error al obtener los viajes");
}

$stops_map = [];
foreach ($stops_data['stops'] as $stop) {
    $stops_map[$stop['id']] = $stop['n'];
}

foreach ($routes_data['routes'] as $route) {
    $route_id = $route['id'];
    $route_name = $route['n'];
    $url_report = "$url_report_base$route_id?flags=1&df=$date&dt=$date&sort=timetable";
    $report_data = getApiData($url_report, $token);

    if (!$report_data || !isset($report_data['report_data']['rows'])) {
        echo "<h3>Error al obtener datos de la ruta: $route_name</h3>";
        continue;
    }

    echo "<h3>Ruta: $route_name</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID Unidad</th>";
    
    $stops = [];
    foreach ($report_data['report_data']['rows'][0]['rows'] as $stop_data) {
        if (isset($stop_data[0]['t'])) {
            $stops[] = $stop_data[0]['t'];
            echo "<th colspan='3'>{$stop_data[0]['t']}</th>";
        }
    }
    echo "</tr><tr><th></th>";
    foreach ($stops as $stop) {
        echo "<th>Plan</th><th>Eje</th><th>Dif</th>";
    }
    echo "</tr>";

    foreach ($report_data['report_data']['rows'] as $unit_data) {
        $unit_name = $unit_data['cols'][0]['t'];
        echo "<tr><td>$unit_name</td>";

        foreach ($unit_data['rows'] as $stop_data) {
            $plan_time = isset($stop_data[3]['t']) ? $stop_data[3]['t'] : "--:--";
            $exec_time = isset($stop_data[4]['t']) ? $stop_data[4]['t'] : "--:--";
            
            $diff = "-";
            if ($plan_time !== "--:--" && $exec_time !== "--:--") {
                $diff = strtotime($exec_time) - strtotime($plan_time);
                $diff = round($diff / 60);
            }
            echo "<td>$plan_time</td><td>$exec_time</td><td>$diff</td>";
        }
        echo "</tr>";
    }
    echo "</table><br>";
}
