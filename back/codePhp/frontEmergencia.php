<?php

// Configuración de la conexión a la base de datos
$host = '127.0.0.1';
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
} catch (PDOException $e) {
    echo 'Error en la conexión: ' . $e->getMessage();
    exit();
}

// Actualizar valor_vuelta si se envía el formulario
if (isset($_POST['editar'])) {
    $id_produccion = (int) $_POST['id_produccion'];
    $valor_vuelta = $_POST['valor_vuelta'];

    if (preg_match('/^\d+(\.\d{2})$/', $valor_vuelta)) {
        $stmt = $pdo->prepare("UPDATE produccion SET valor_vuelta = ? WHERE id_produccion = ?");
        $stmt->execute([$valor_vuelta, $id_produccion]);
        echo "<div style='color: green;'>Valor actualizado correctamente.</div>";
    } else {
        echo "<div style='color: red;'>Error: El valor debe tener el formato 20.00 (decimal con punto).</div>";
    }
}

// Listar todos los registros de la tabla produccion
$stmt = $pdo->query("SELECT * FROM produccion");
$produccion = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Valor Vuelta</title>
</head>
<body>
    <h1>Editar Valor Vuelta</h1>

    <table border="1">
        <thead>
            <tr>
                <th>ID Producción</th>
                <th>ID Hoja</th>
                <th>Nro Vuelta</th>
                <th>Hora Subida</th>
                <th>Hora Bajada</th>
                <th>Valor Vuelta</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produccion as $fila): ?>
                <tr>
                    <form method="POST" action="">
                        <td><?= htmlspecialchars($fila['id_produccion']) ?></td>
                        <td><?= htmlspecialchars($fila['id_hoja']) ?></td>
                        <td><?= htmlspecialchars($fila['nro_vuelta']) ?></td>
                        <td><?= htmlspecialchars($fila['hora_subida']) ?></td>
                        <td><?= htmlspecialchars($fila['hora_bajada']) ?></td>
                        <td><input type="text" name="valor_vuelta" value="<?= htmlspecialchars($fila['valor_vuelta']) ?>" required></td>
                        <td>
                            <input type="hidden" name="id_produccion" value="<?= $fila['id_produccion'] ?>">
                            <input type="submit" name="editar" value="Guardar">
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
