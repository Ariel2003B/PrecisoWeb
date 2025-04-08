<?php

require '../vendor/autoload.php'; // Para usar PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Configuración de la conexión a la base de datos
$host = '132.148.176.238';
$db = 'dbPrecisoGps';
$user = 'precisogps';
$pass = 'Preciso2024!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}

$numerosCSV = [];
$numerosNoEncontrados = [];

if (isset($_POST['submit']) && isset($_FILES['archivo'])) {
    $file = $_FILES['archivo']['tmp_name'];
    $fileHandle = fopen($file, 'r');

    if ($fileHandle !== false) {
        while (($data = fgetcsv($fileHandle, 1000, ";")) !== false) {
            $numero = trim($data[0]); // Se asume que cada número está en la primera columna del CSV
            if (!empty($numero)) {
                $numerosCSV[] = $numero;
            }
        }
        fclose($fileHandle);

        // Consulta a la base de datos para obtener los números existentes
        $placeholders = implode(',', array_fill(0, count($numerosCSV), '?'));
        $sql = "SELECT NUMEROTELEFONO FROM SIMCARD WHERE NUMEROTELEFONO IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($numerosCSV);
        $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Encontrar los números que no están en la base de datos
        $numerosNoEncontrados = array_diff($numerosCSV, $resultados);

        // Si hay números no encontrados, generamos el archivo Excel
        if (!empty($numerosNoEncontrados)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Números No Encontrados');
            $sheet->setCellValue('A1', 'Número de Teléfono');
            
            $row = 2;
            foreach ($numerosNoEncontrados as $numero) {
                $sheet->setCellValue("A$row", $numero);
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'numeros_no_encontrados.xlsx';
            $writer->save($fileName);

            // Descargar el archivo generado
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            readfile($fileName);

            // Eliminar el archivo temporal
            unlink($fileName);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comparar Números de Teléfono</title>
</head>
<body>

<h2>Subir archivo CSV para comparar números de teléfono</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="archivo" accept=".csv" required>
    <button type="submit" name="submit">Comparar</button>
</form>

</body>
</html>
