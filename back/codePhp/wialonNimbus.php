<?php
// Parámetros Wialon
$baseUrl = "https://hst-api.wialon.com/wialon/ajax.html";
$sid = "41ede2a7015dac5ec646b433e96b24ea"; // Reemplazar con tu SID válido
$reportResourceId = 401683210;
$reportTemplateId = 2;
$reportObjectId = 401691226;
$reportObjectSecId = 0;

// Obtener timestamp de hoy desde las 00:00 hasta las 23:59
date_default_timezone_set('America/Guayaquil'); // Ajustar según zona horaria
$from = strtotime("today");
$to = strtotime("tomorrow") - 1;

// Ejecutar reporte
$execParams = [
    "reportResourceId" => $reportResourceId,
    "reportTemplateId" => $reportTemplateId,
    "reportObjectId" => $reportObjectId,
    "reportObjectSecId" => $reportObjectSecId,
    "interval" => [
        "from" => $from,
        "to" => $to,
        "flags" => 0
    ],
    "remoteExec" => 0
];

$execResult = wialonRequest("report/exec_report", $execParams, $sid);

// Esperar unos segundos antes de obtener resultados
sleep(3);

// Obtener filas de resultados (primeras 500)
$getRowsParams = [
    "tableIndex" => 0,
    "indexFrom" => 0,
    "indexTo" => 500
];

$rowsResult = wialonRequest("report/get_result_rows", $getRowsParams, $sid);

// Mostrar resultados
echo "<h2>Geocercas y horas de paso de la unidad para hoy</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>#</th><th>Geocerca</th><th>Hora de paso</th></tr>";

foreach ($rowsResult as $row) {
    $num = $row['c'][0];
    $nombre = $row['c'][1];
    $horaPaso = $row['c'][2]['t'];
    echo "<tr><td>$num</td><td>$nombre</td><td>$horaPaso</td></tr>";
}
echo "</table>";

// Función para hacer la solicitud a Wialon
function wialonRequest($svc, $params, $sid) {
    $url = "https://hst-api.wialon.com/wialon/ajax.html?svc=" . urlencode($svc);
    $postFields = [
        'params' => json_encode($params),
        'sid' => $sid
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
?>
