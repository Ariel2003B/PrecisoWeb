<?php
ini_set('max_execution_time', 1200); // Aumenta el tiempo de ejecución a 20 minutos

// Configuración
$session_id = "41c957e5eb4fec9c449718ea399c3796"; // Sesión de Wialon
$wialon_api_url = "https://hst-api.wialon.com/wialon/ajax.html";

// Conexión a la base de datos
$host = "132.148.176.238";
$dbname = "dbPrecisoGps";
$username = "precisogps";
$password = "Preciso2024!";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}

// 1. Obtener todas las unidades desde Wialon con UID (IMEI)
$params = json_encode([
    "spec" => [
        "itemsType" => "avl_unit",
        "propName" => "sys_name",
        "propValueMask" => "*",
        "sortType" => "sys_name"
    ],
    "force" => 1,
    "flags" => 256, // Obtener UID
    "from" => 0,
    "to" => 0
]);

$url_get_units = "$wialon_api_url?svc=core/search_items&params=" . urlencode($params) . "&sid=$session_id";
$response = file_get_contents($url_get_units);
$data = json_decode($response, true);

if (!isset($data["items"]) || empty($data["items"])) {
    die("❌ No se encontraron unidades o hubo un error en la respuesta de Wialon.");
}

// 2. Obtener los IMEIs y números de teléfono desde la base de datos
$sql = "SELECT IMEI, NUMEROTELEFONO FROM SIMCARD"; // Reemplaza con el nombre de tu tabla
$stmt = $pdo->prepare($sql);
$stmt->execute();
$database_imeis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convertir los datos de la base en un array asociativo IMEI => NUMEROTELEFONO
$imei_phone_map = [];
foreach ($database_imeis as $row) {
    $imei_phone_map[$row["IMEI"]] = $row["NUMEROTELEFONO"];
}

// 3. Buscar coincidencias de UID en Wialon con IMEI en la base de datos
$matched_units = [];
foreach ($data["items"] as $unit) {
    if (!isset($unit["uid"]) || empty($unit["uid"])) {
        continue; // Omitir unidades sin UID
    }

    $imei = $unit["uid"];
    if (isset($imei_phone_map[$imei])) {
        $matched_units[$imei] = $imei_phone_map[$imei]; // Guardamos las coincidencias
    }
}

// Si no hay coincidencias, terminamos la ejecución
if (empty($matched_units)) {
    die("⚠️ No se encontraron coincidencias entre los UID de Wialon y los IMEIs en la base de datos.");
}

// 4. Obtener el itemId correspondiente a cada UID encontrado en Wialon
foreach ($matched_units as $imei => $phone) {
    // Obtener itemId de la unidad en Wialon usando UID
    $params_item = json_encode([
        "spec" => [
            "itemsType" => "avl_unit",
            "propName" => "sys_unique_id",
            "propValueMask" => $imei,
            "sortType" => "sys_name"
        ],
        "force" => 1,
        "flags" => 1,
        "from" => 0,
        "to" => 0
    ]);

    $url_get_item = "$wialon_api_url?svc=core/search_items&params=" . urlencode($params_item) . "&sid=$session_id";
    $item_response = file_get_contents($url_get_item);
    $item_data = json_decode($item_response, true);

    if (!isset($item_data["items"][0]["id"])) {
        echo "❌ No se encontró itemId para IMEI '$imei'. Se omite.<br>";
        continue;
    }

    $item_id = $item_data["items"][0]["id"];
    $new_phone = "+593" . $phone;

    // 5. Enviar solicitud a Wialon para actualizar el número de teléfono
    $params_update = json_encode([
        "itemId" => $item_id,
        "phoneNumber" => $new_phone
    ]);

    $url_update = "$wialon_api_url?svc=unit/update_phone&params=" . urlencode($params_update) . "&sid=$session_id";
    $update_response = file_get_contents($url_update);
    $update_data = json_decode($update_response, true);

    if (isset($update_data["error"])) {
        echo "❌ Error actualizando teléfono para IMEI '$imei'. Código: " . $update_data["error"] . "<br>";
    } else {
        echo "✅ Teléfono actualizado para IMEI '$imei' (Número: $new_phone).<br>";
    }
}
?>
