<?php

// Configuración de la base de datos
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

// Ruta del archivo CSV
$csvFile = 'MigracionEmergente.csv';

if (!file_exists($csvFile)) {
    die("El archivo CSV no existe.");
}

// Preparar la consulta SQL
$sql = "INSERT INTO SIMCARD (
    GRUPO,
    ASIGNACION,
    IMEI,
    ICC
) VALUES (
    :GRUPO,
    :ASIGNACION,
    :IMEI,
    :ICC
)";
$stmt = $pdo->prepare($sql);

// Leer el archivo CSV
if (($handle = fopen($csvFile, 'r')) !== false) {
    // Omitir la primera línea (encabezados)
    fgetcsv($handle, 1000, ';');

    while (($data = fgetcsv($handle, 1000, ';')) !== false) {
        // Limpiar los valores y asignar
        $grupo = !empty($data[0]) ? $data[0] : null;
        $asignacion = !empty($data[1]) ? $data[1] : null;
        $imei = !empty($data[2]) ? str_replace("'", "", $data[2]) : null;
        $icc = !empty($data[3]) ? rtrim($data[3], 'F') : null;

        // Vincular parámetros y ejecutar la consulta
        $stmt->bindParam(':GRUPO', $grupo);
        $stmt->bindParam(':ASIGNACION', $asignacion);
        $stmt->bindParam(':IMEI', $imei);
        $stmt->bindParam(':ICC', $icc);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error al insertar fila: " . $e->getMessage() . "\n";
        }
    }

    fclose($handle);
    echo "Migración completada con éxito.";
} else {
    echo "No se pudo abrir el archivo CSV.";
}
?>
