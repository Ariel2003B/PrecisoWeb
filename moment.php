<?php
// Conexión a la base de datos
// $host = 'localhost:3307';
// $dbname = 'dbPrecisoPruebas';
// $username = 'root';
// $password = 'Ariel2003B';

$host = '132.148.176.238';
$dbname = 'dbPrecisoGps';
$username = 'precisogps';
$password = 'Preciso2024!';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ruta del archivo CSV
    $archivoCsv = 'grupos.csv'; // Cambia por la ruta del archivo CSV que subiste

    if (($handle = fopen($archivoCsv, "r")) !== false) {
        // Leer el CSV línea por línea
        while (($data = fgetcsv($handle, 1000, ";")) !== false) {
            $asignacion = trim($data[0]); // Primera columna (Asignación)
            $grupo = trim($data[1]); // Segunda columna (Grupo)

            // Verificar si la asignación existe en la base de datos
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM SIMCARD WHERE ASIGNACION = :asignacion");
            $stmt->execute([':asignacion' => $asignacion]);
            $existe = $stmt->fetchColumn();

            if ($existe) {
                // Actualizar el grupo si la asignación existe
                $updateStmt = $pdo->prepare("UPDATE SIMCARD SET GRUPO = :grupo WHERE ASIGNACION = :asignacion");
                $updateStmt->execute([
                    ':grupo' => $grupo,
                    ':asignacion' => $asignacion
                ]);
                echo "Asignación '$asignacion' actualizada con grupo '$grupo'.\n";
            } else {
                echo "Asignación '$asignacion' no encontrada. No se realizó ningún cambio.\n";
            }
        }
        fclose($handle);
    } else {
        echo "Error al abrir el archivo CSV.\n";
    }
} catch (PDOException $e) {
    echo "Error de conexión a la base de datos: " . $e->getMessage();
}
