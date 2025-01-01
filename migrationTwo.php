<?php

// Configuración de la base de datos
// $host = 'localhost:3307';
// $db = 'dbPrecisoPruebas';
// $user = 'root';
// $password = 'Ariel2003B';
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
$csvFile = 'domingoMigration.csv';

if (!file_exists($csvFile)) {
    die("El archivo CSV no existe.");
}

// Preparar las consultas SQL
$sqlUpdate = "UPDATE SIMCARD SET
    PROPIETARIO = :PROPIETARIO,
    CUENTA = :CUENTA,
    NUMEROTELEFONO = :NUMEROTELEFONO,
    PLAN = :PLAN,
    TIPOPLAN = :TIPOPLAN
WHERE ICC = :ICC";
$stmtUpdate = $pdo->prepare($sqlUpdate);

$sqlInsert = "INSERT INTO SIMCARD (PROPIETARIO, CUENTA, NUMEROTELEFONO, PLAN, TIPOPLAN, ICC, ESTADO)
VALUES (:PROPIETARIO, :CUENTA, :NUMEROTELEFONO, :PLAN, :TIPOPLAN, :ICC, 'LIBRE')";
$stmtInsert = $pdo->prepare($sqlInsert);

// Leer el archivo CSV
$totalRegistros = 0;
$registrosActualizados = 0;
$registrosInsertados = 0;

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

        try {
            // Intentar actualizar el registro
            $stmtUpdate->bindParam(':PROPIETARIO', $propietario);
            $stmtUpdate->bindParam(':CUENTA', $cuenta);
            $stmtUpdate->bindParam(':NUMEROTELEFONO', $telefono);
            $stmtUpdate->bindParam(':PLAN', $descripcionPlan);
            $stmtUpdate->bindParam(':TIPOPLAN', $plan);
            $stmtUpdate->bindParam(':ICC', $simcard);
            $stmtUpdate->execute();

            if ($stmtUpdate->rowCount() > 0) {
                $registrosActualizados++;
            } else {
                // Si no se encuentra, insertar como nuevo
                $stmtInsert->bindParam(':PROPIETARIO', $propietario);
                $stmtInsert->bindParam(':CUENTA', $cuenta);
                $stmtInsert->bindParam(':NUMEROTELEFONO', $telefono);
                $stmtInsert->bindParam(':PLAN', $descripcionPlan);
                $stmtInsert->bindParam(':TIPOPLAN', $plan);
                $stmtInsert->bindParam(':ICC', $simcard);
                $stmtInsert->execute();
                $registrosInsertados++;
            }
        } catch (PDOException $e) {
            echo "Error al procesar fila: " . $e->getMessage() . "\n";
        }
    }

    fclose($handle);

    // Mostrar el resumen
    echo "Total de registros procesados: $totalRegistros\n";
    echo "Registros actualizados: $registrosActualizados\n";
    echo "Registros insertados: $registrosInsertados\n";
} else {
    echo "No se pudo abrir el archivo CSV.";
}
?>
