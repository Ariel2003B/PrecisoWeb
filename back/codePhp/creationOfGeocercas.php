<?php

// URL y Token para obtener paradas de Nimbus
$nimbus_url = "https://nimbus.wialon.com/api/depot/8994/stops";
$auth_token = "3f0dbfdfc321494c9dadc12ceea79d3d"; // Token de autorización

// Realizar la petición a Nimbus
$ch = curl_init($nimbus_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Token $auth_token"
]);
$response = curl_exec($ch);
curl_close($ch);

// Decodificar la respuesta JSON
$data = json_decode($response, true);

// Verificar que tenemos las paradas
if (!isset($data['stops'])) {
    die("Error: No se pudieron obtener las paradas.");
}

// Configuración de la API de Wialon
$wialon_base_url = "https://hst-api.wialon.com/wialon/ajax.html";
$sid = "4106b4f117ef3c3ebb286d5d4809b5a4"; // Session ID de Wialon
$itemId = 401275156; // ID del recurso en Wialon

// Grupos de geocercas
$grupos = [
    'num' => 1,  // RUTA CA6
    'letra' => 2, // RUTA CA7
    'nombre' => 3 // RUTA CA8
];

// Colores para cada tipo
$colores = [
    'num' => 255,   // Rojo (Ejemplo)
    'letra' => 65280, // Verde (Ejemplo)
    'nombre' => 16776960 // Azul (Ejemplo)
];

// Iterar sobre las paradas para crear geocercas
foreach ($data['stops'] as $parada) {
    $nombre = $parada['n'];
    $descripcion = $parada['d'];
    $coordenadas = $parada['p'];
    $radio = isset($coordenadas[0]['r']) ? $coordenadas[0]['r'] : 30;
    
    // Determinar grupo y color
    if (preg_match('/^\d/', $nombre)) {
        $grupo = $grupos['num'];
        $color = $colores['num'];
    } elseif (preg_match('/^[a-z]/', $nombre)) {
        $grupo = $grupos['letra'];
        $color = $colores['letra'];
    } else {
        $grupo = $grupos['nombre'];
        $color = $colores['nombre'];
    }
    
    // Crear geocerca en Wialon
    $geocerca_data = [
        "itemId" => $itemId,
        "id" => 0,
        "callMode" => "create",
        "n" => $nombre,
        "d" => $descripcion,
        "t" => 3,
        "w" => $radio,
        "f" => 32,
        "c" => $color,
        "p" => [[
            "x" => $coordenadas[0]['x'],
            "y" => $coordenadas[0]['y'],
            "r" => $radio
        ]]
    ];
    
    $ch = curl_init("$wialon_base_url?svc=resource/update_zone&params=" . urlencode(json_encode($geocerca_data)) . "&sid=$sid");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $geo_response = curl_exec($ch);
    curl_close($ch);
    
    $geo_result = json_decode($geo_response, true);
    if (!isset($geo_result[1]['id'])) {
        echo "Error al crear geocerca: $nombre\n";
        continue;
    }
    
    $geo_id = $geo_result[1]['id'];
    
    // Asignar geocerca al grupo
    $grupo_data = [
        "itemId" => $itemId,
        "id" => $grupo,
        "callMode" => "update",
        "zns" => [$geo_id]
    ];
    
    $ch = curl_init("$wialon_base_url?svc=resource/update_zones_group&params=" . urlencode(json_encode($grupo_data)) . "&sid=$sid");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $group_response = curl_exec($ch);
    curl_close($ch);
    
    echo "Geocerca '$nombre' creada y asignada al grupo $grupo.\n";
}

?>
