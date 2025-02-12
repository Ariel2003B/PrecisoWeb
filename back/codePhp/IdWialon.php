<?php

// Configuración de la base de datos
$host = "132.148.176.238";
$dbname = "dbPrecisoGps";
$username = "precisogps";
$password = "Preciso2024!";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// URL de la API de Nimbus Wialon
$apiUrl = 'https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={"spec":{"itemsType":"avl_unit","propName":"sys_name","propValueMask":"*","sortType":"sys_name"},"force":1,"flags":1025,"from":0,"to":0}&sid=41752fa64beafc8117d33bb210a33c40';

// Obtener datos desde la API
$response = file_get_contents($apiUrl);
if ($response === false) {
    die("Error al obtener datos de la API");
}

$data = json_decode($response, true);
if (!isset($data["items"]) || !is_array($data["items"])) {
    die("Formato de respuesta de API no válido");
}

// Recorrer los elementos de la API
foreach ($data["items"] as $item) {
    $nm = $item["nm"];
    $id_wialon = $item["id"];

    // Buscar coincidencias en la base de datos
    $stmt = $pdo->prepare("UPDATE SIMCARD SET ID_WIALON = :id_wialon WHERE ASIGNACION = :nm");
    $stmt->execute([
        ':id_wialon' => $id_wialon,
        ':nm' => $nm
    ]);

    if ($stmt->rowCount() > 0) {
        echo "Registro actualizado: ASIGNACION = $nm, ID_WIALON = $id_wialon\n";
    }
}

echo "Proceso completado.";

?>
