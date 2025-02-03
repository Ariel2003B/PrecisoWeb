<?php
ini_set('max_execution_time', 600); // Aumenta el tiempo de ejecución a 5 minutos

// Configuración
$session_id = "417a5f4e4600e51de0fcd00601085e33";
$wialon_api_url = "https://hst-api.wialon.com/wialon/ajax.html";

// 1. Obtener todas las unidades
$params = json_encode([
    "spec" => [
        "itemsType" => "avl_unit",
        "propName" => "sys_name",
        "propValueMask" => "*",
        "sortType" => "sys_name"
    ],
    "force" => 1,
    "flags" => 1,
    "from" => 0,
    "to" => 0
]);

$url_get_units = "$wialon_api_url?svc=core/search_items&params=" . urlencode($params) . "&sid=$session_id";
$response = file_get_contents($url_get_units);
$data = json_decode($response, true);

if (!isset($data["items"])) {
    die("No se encontraron unidades o hubo un error.");
}

// 2. Recorrer todas las unidades y eliminar los números de teléfono
foreach ($data["items"] as $unit) {
    $item_id = $unit["id"];
    $unit_name = $unit["nm"];

    $params_update = json_encode([
        "itemId" => $item_id,
        "phoneNumber" => ""  // Dejar el número vacío para eliminarlo
    ]);

    $url_update = "$wialon_api_url?svc=unit/update_phone&params=" . urlencode($params_update) . "&sid=$session_id";
    $update_response = file_get_contents($url_update);
    $update_data = json_decode($update_response, true);

    if (isset($update_data["error"])) {
        echo "❌ Error eliminando teléfono de la unidad '$unit_name' (ID: $item_id). Código de error: " . $update_data["error"] . "<br>";
    } else {
        echo "✅ Teléfono eliminado para la unidad '$unit_name' (ID: $item_id).<br>";
    }
}
?>
