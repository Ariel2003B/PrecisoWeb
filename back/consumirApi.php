
<?php

//Configuración de la base de datos
// $host = 'localhost:3307';
// $db = 'dbPrecisoPruebas';
// $user = 'root';
// $password = 'Ariel2003B';
$host = '132.148.176.238';
$db = 'dbPrecisoGps';
$user = 'precisogps';
$password = 'Preciso2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Token para autenticación
$token = "a21e2472955b1cb0847730f34edcf3e8E1E1FA550AEDE0FF779FF697EA72831E24B0F2F8";
$authUrl = "https://hst-api.wialon.com/wialon/ajax.html?svc=token/login&params={\"token\":\"$token\"}";

// Paso 1: Obtener el SID
$sid = null;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $authUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);
if (isset($responseData['eid'])) {
    $sid = $responseData['eid'];
} else {
    die("Error al obtener el SID: " . $response);
}

// Paso 2: Consultar todos los ítems
$itemsUrl = "https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params=%7B%22spec%22%3A%7B%22itemsType%22%3A%22avl_unit%22%2C%22propName%22%3A%22sys_name%22%2C%22propValueMask%22%3A%22*%22%2C%22sortType%22%3A%22sys_name%22%7D%2C%22force%22%3A1%2C%22flags%22%3A4611686018427387903%2C%22from%22%3A0%2C%22to%22%3A0%7D&sid=$sid";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $itemsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$itemsData = json_decode($response, true);
if (!isset($itemsData['items'])) {
    die("Error al obtener los ítems: " . $response);
}

// Librería para generar PDFs
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Crear una instancia de Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

// Generar contenido HTML para el PDF
$html = "<h1>Logs de Actualización</h1>";
$html .= "<table border='1' cellpadding='5' cellspacing='0'>";
$html .= "<thead><tr><th>Asignación</th><th>IMEI</th><th>ICC</th><th>Estado</th></tr></thead><tbody>";

$updatedCount = 0;
$skippedCount = 0;

foreach ($itemsData['items'] as $item) {
    $nm = isset($item['nm']) ? $item['nm'] : 'N/A';
    $imei = isset($item['uid']) ? $item['uid'] : null;
    $icc = isset($item['prms']['iccid']['v']) ? rtrim($item['prms']['iccid']['v'], 'F') : null;

    if (!$imei || !$icc) {
        $html .= "<tr><td>$nm</td><td>" . ($imei ?: 'N/A') . "</td><td>" . ($icc ?: 'N/A') . "</td><td>Omitido: Falta IMEI o ICC</td></tr>";
        $skippedCount++;
        continue;
    }

    // Paso 3: Comprobar si el IMEI ya existe en la base de datos
    $sqlCheck = "SELECT COUNT(*) FROM SIMCARD WHERE ICC = :ICC";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindParam(':ICC', $icc);
    $stmtCheck->execute();
    $exists = $stmtCheck->fetchColumn();

    if ($exists > 0) {
        // Paso 4: Actualizar el registro con el ICC
        $sqlUpdate = "UPDATE SIMCARD SET ASIGNACION = :ASIGNACION, IMEI = :IMEI, EQUIPO = :EQUIPO WHERE ICC = :ICC";
        $estado='GPS';
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':ICC', $icc);
        $stmtUpdate->bindParam(':IMEI', $imei);
        $stmtUpdate->bindParam(':ASIGNACION', $nm);
        $stmtUpdate->bindParam(':EQUIPO', $estado);

        try {
            $stmtUpdate->execute();
            $html .= "<tr><td>$nm</td><td>$imei</td><td>$icc</td><td>Actualizado</td></tr>";
            $updatedCount++;
        } catch (PDOException $e) {
            $html .= "<tr><td>$nm</td><td>$imei</td><td>$icc</td><td>Error: " . $e->getMessage() . "</td></tr>";
        }
    } else {
        $html .= "<tr><td>$nm</td><td>$imei</td><td>$icc</td><td>No existe en BD</td></tr>";
    }
}

$html .= "</tbody></table>";
$html .= "<p>Total Actualizados: $updatedCount</p>";
$html .= "<p>Total Omitidos: $skippedCount</p>";

// Configurar el contenido HTML en Dompdf
$dompdf->loadHtml($html);

// Renderizar el PDF
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Descargar el archivo PDF
$dompdf->stream('logs_actualizacion.pdf', ["Attachment" => true]);
?>
