<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = "";
    $resultados = [];

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $archivoTemporal = $_FILES['archivo']['tmp_name'];

        // Validar el tipo de archivo
        $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        if ($extension !== 'csv') {
            $mensaje = "El archivo debe ser un archivo CSV.";
        } else {
            // Procesar el archivo CSV
            if (($handle = fopen($archivoTemporal, 'r')) !== false) {
                $cabeceras = fgetcsv($handle, 0, ';'); // Leer encabezados

                while (($fila = fgetcsv($handle, 0, ';')) !== false) {
                    // Procesar cada fila
                    $unidad = $fila[0];
                    $placa = $fila[1];
                    $minutosGeocercas = array_slice($fila, 4); // Columnas de geocercas

                    $sanciones = [];
                    foreach ($minutosGeocercas as $valor) {
                        $valor = trim($valor);
                        if (str_starts_with($valor, '+')) {
                            $sanciones[] = 0;
                        } elseif (str_starts_with($valor, '-')) {
                            $sanciones[] = 1;
                        } else {
                            $sanciones[] = 0;
                        }
                    }

                    $totalSanciones = array_sum($sanciones);

                    $resultados[] = [
                        'unidad' => $unidad,
                        'placa' => $placa,
                        'sanciones' => $totalSanciones
                    ];
                }

                fclose($handle);
                $mensaje = "Archivo procesado correctamente.";
            } else {
                $mensaje = "No se pudo leer el archivo CSV.";
            }
        }
    } else {
        $mensaje = "Error al cargar el archivo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar CSV</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Cargar y Procesar CSV</h1>

        <!-- Formulario para cargar el archivo -->
        <form action="" method="POST" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label for="archivo" class="form-label">Seleccionar archivo CSV</label>
                <input type="file" name="archivo" id="archivo" class="form-control" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Procesar</button>
        </form>

        <!-- Mostrar mensajes -->
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info mt-4"> <?php echo $mensaje; ?> </div>
        <?php endif; ?>

        <!-- Mostrar resultados -->
        <?php if (!empty($resultados)): ?>
            <h2 class="mt-5">Resultados Procesados</h2>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Unidad</th>
                        <th>Placa</th>
                        <th>Total Sanciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $resultado): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($resultado['unidad']); ?></td>
                            <td><?php echo htmlspecialchars($resultado['placa']); ?></td>
                            <td><?php echo htmlspecialchars($resultado['sanciones']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
