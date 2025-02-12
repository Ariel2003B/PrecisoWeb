<?php

$api_nimbus = "https://nimbus.wialon.com/api/depot/8994/stops";
$token = "3f0dbfdfc321494c9dadc12ceea79d3d";
//$token = "3f0dbfdfc321494c9dadc12ceea79d3d";
$headers = [
    "Authorization: Token $token",
    "Content-Type: application/json"
];

// Obtener los primeros 3 registros de Nimbus
$ch = curl_init($api_nimbus);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
//$stops = array_slice($data['stops'], 0, 1); // Tomamos solo los primeros 3
$stops = $data['stops']; // Tomamos solo los primeros 3

$itemId = 401275156; // ID del recurso en Wialon
$sessionId = "41c0468d899bb62d88aa0e20ff12ef7f"; // SID válido para la sesión

// Configuración de grupos
$groups = [
    'number' => 1, // Nombres con número al inicio -> RUTA CA6
    'letter' => 2, // Nombres con letra al inicio -> RUTA CA7
    'plain' => 3   // Solo nombre -> RUTA CA8
];

// Configuración de colores según tipo de nombre
$colors = [
    'number' => 0x80FF0000,  // 🔴 Rojo con 50% de transparencia (0x80FF0000)
    'letter' => 0x800000FF,  // 🔵 Azul con 50% de transparencia (0x800000FF)
    'plain' => 0x8000FF00    // 🟢 Verde con 50% de transparencia (0x8000FF00)
];
// Almacenamos las geocercas creadas para cada grupo
$geofences_by_group = [
    1 => [], // RUTA CA6
    2 => [], // RUTA CA7
    3 => []  // RUTA CA8
];

foreach ($stops as $stop) {
    $name = $stop['n'];
    $description = $stop['d'];
    $shape = $stop['sh']; // 0 = círculo, 1 = polígono
    $coordinates = $stop['p']; // Todas las coordenadas

    // **Validaciones del nombre**
    if (preg_match('/^\d+\.\s/', $name)) {
        // Formato `1. Nombre`, **NO insertar**.
        echo "Omitiendo geocerca: " . $name . " (Formato inválido)\n";
        continue;
    }

    // **Determinar grupo y color**
    if (preg_match('/^\d+/', $name)) {
        $group_id = $groups['number']; // CA6
        $color = $colors['number'];
    } elseif (preg_match('/^[a-z]/', $name)) {
        $group_id = $groups['letter']; // CA7
        $color = $colors['letter'];
    } else {
        $group_id = $groups['plain']; // CA8
        $color = $colors['plain'];
    }

    // **Definir tipo de geocerca y estructura del JSON**
    $params = [
        "itemId" => $itemId,
        "id" => 0,
        "callMode" => "create",
        "n" => $name,
        "d" => $description,
        "t" => ($shape == 0) ? 3 : 2,  // 3 = círculo, 2 = polígono
        "f" => 33,
        "c" => $color,
        "w" => ($shape == 0) ? ($coordinates[0]['r'] > 0 ? $coordinates[0]['r'] : 20) : 0 // Asegurar w=0 en polígonos
    ];

    if ($shape == 0) { // **Círculo**
        $params["p"] = [
            [
                "x" => $coordinates[0]['x'],
                "y" => $coordinates[0]['y'],
                "r" => ($coordinates[0]['r'] > 0) ? $coordinates[0]['r'] : 20
            ]
        ];
    } else { // **Polígono**
        $points = [];
        foreach ($coordinates as $coord) {
            $points[] = [
                "x" => $coord['x'],
                "y" => $coord['y'],
                "r" => 0 // Wialon requiere "r" en cada punto
            ];
        }
        $params["p"] = $points;
    }

    // **Convertir JSON**
    $params_json = json_encode($params, JSON_UNESCAPED_SLASHES);

    // **URL de solicitud**
    $create_geofence_url = "https://hst-api.wialon.com/wialon/ajax.html?svc=resource/update_zone&params=" . urlencode($params_json) . "&sid=$sessionId";

    echo "Enviando JSON a Wialon:\n" . $params_json . "\n";

    $ch = curl_init($create_geofence_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if ($response === false) {
        echo "cURL Error: " . curl_error($ch) . "\n";
    }

    curl_close($ch);

    $result = json_decode($response, true);
    echo "Respuesta de Wialon:\n";
    print_r($result);

    if (isset($result[1]['id'])) {
        $geo_id = $result[1]['id'];
        $geofences_by_group[$group_id][] = $geo_id; // Guardamos el ID en su grupo correspondiente
        echo "Geocerca creada: " . $name . " (ID: $geo_id)\n";
    } else {
        echo "Error creando geocerca: " . $name . "\n";
    }
}

// **Asignar geocercas a grupos (una sola solicitud por grupo)**
foreach ($geofences_by_group as $group_id => $geo_ids) {
    if (!empty($geo_ids)) {
        $geo_ids = array_map('intval', $geo_ids);

        $params = json_encode([
            "itemId" => $itemId,
            "id" => $group_id,
            "callMode" => "update",
            "n" => "RUTA CA" . ($group_id + 5),
            "d" => "",
            "zns" => $geo_ids
        ], JSON_UNESCAPED_SLASHES);

        $assign_group_url = "https://hst-api.wialon.com/wialon/ajax.html?svc=resource/update_zones_group&params=" . urlencode($params) . "&sid=$sessionId";

        echo "URL Generada: " . $assign_group_url . "\n";
        echo "Payload: " . $params . "\n";

        $ch = curl_init($assign_group_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        echo "Respuesta del servidor: " . $response . "\n";
    }
}

?>