<?php

// Configuración de la base de datos
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

// Ruta del archivo CSV
$csvFile = 'Migracion3.csv';

if (!file_exists($csvFile)) {
    die("El archivo CSV no existe.");
}

// Preparar la consulta SQL para actualizar datos
$sqlUpdate = "UPDATE SIMCARD SET
    PROPIETARIO = :PROPIETARIO,
    CUENTA = :CUENTA,
    NUMEROTELEFONO = :NUMEROTELEFONO,
    PLAN = :PLAN,
    TIPOPLAN = :TIPOPLAN
WHERE ICC = :ICC";
$stmtUpdate = $pdo->prepare($sqlUpdate);

// Leer el archivo CSV
$totalRegistros = 0;
$registrosActualizados = 0;
$registrosNoEncontrados = 0;

if (($handle = fopen($csvFile, 'r')) !== false) {
    // Omitir la primera línea (encabezados)
    fgetcsv($handle, 1000, ';');

    while (($data = fgetcsv($handle, 1000, ';')) !== false) {
        $totalRegistros++;

        // Limpiar los valores
        $propietario = !empty($data[0]) ? $data[0] : null;
        $cuenta = !empty($data[1]) ? $data[1] : null;
        $telefono = !empty($data[2]) ? $data[2] : null;
        $plan = !empty($data[3]) ? $data[3] : null;
        $descripcionPlan = !empty($data[4]) ? $data[4] : null;
        $simcard = !empty($data[5]) ? str_replace("'", "", $data[5]) : null;

        // Intentar actualizar el registro
        $stmtUpdate->bindParam(':PROPIETARIO', $propietario);
        $stmtUpdate->bindParam(':CUENTA', $cuenta);
        $stmtUpdate->bindParam(':NUMEROTELEFONO', $telefono);
        $stmtUpdate->bindParam(':PLAN', $descripcionPlan);
        $stmtUpdate->bindParam(':TIPOPLAN', $plan);
        $stmtUpdate->bindParam(':ICC', $simcard);

        try {
            $stmtUpdate->execute();
            if ($stmtUpdate->rowCount() > 0) {
                $registrosActualizados++;
            } else {
                $registrosNoEncontrados++;
            }
        } catch (PDOException $e) {
            echo "Error al actualizar fila: " . $e->getMessage() . "\n";
        }
    }

    fclose($handle);

    // Mostrar el resumen
    echo "Total de registros procesados: $totalRegistros\n";
    echo "Registros actualizados: $registrosActualizados\n";
    echo "Registros no encontrados: $registrosNoEncontrados\n";
} else {
    echo "No se pudo abrir el archivo CSV.";
}
?>
