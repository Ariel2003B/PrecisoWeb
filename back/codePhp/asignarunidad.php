<?php
// Conexión
$host = '132.148.176.238';
$db   = 'dbPrecisoGps';
$user = 'precisogps';
$pass = 'Preciso2024!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    exit("Error de conexión: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usu_id = $_POST['usu_id'];
    $unidad_id = $_POST['unidad_id'];

    $stmt = $pdo->prepare("UPDATE unidades SET usu_id = ? WHERE id_unidad = ?");
    $stmt->execute([$usu_id, $unidad_id]);

    echo "<p style='color: green;'>✅ Unidad asignada correctamente.</p>";
}

$usuarios = $pdo->query("
    SELECT USU_ID, NOMBRE, APELLIDO 
    FROM USUARIO
")->fetchAll();

$unidades = $pdo->query("
    SELECT id_unidad, numero_habilitacion
    FROM unidades 
    WHERE usu_id IS NULL
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Unidad a Conductor</title>
    <!-- CDN de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        label { display: block; margin-top: 15px; }
        select, button { padding: 8px; width: 300px; margin-top: 5px; }
    </style>
</head>
<body>

    <h2>Asignar Unidad a Conductor</h2>

    <?php if (count($usuarios) === 0 || count($unidades) === 0): ?>
        <p style="color: red;">❌ No hay usuarios o unidades disponibles.</p>
    <?php else: ?>
        <form method="POST">
            <label for="usu_id">Selecciona Conductor:</label>
            <select id="usu_id" name="usu_id" required>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['USU_ID'] ?>">
                        <?= $usuario['NOMBRE'] . ' ' . $usuario['APELLIDO'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="unidad_id">Selecciona Unidad Disponible:</label>
            <select name="unidad_id" id="unidad_id" required>
                <?php foreach ($unidades as $unidad): ?>
                    <option value="<?= $unidad['id_unidad'] ?>">
                        <?= $unidad['numero_habilitacion'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <br><br>
            <button type="submit">Asignar Unidad</button>
        </form>
    <?php endif; ?>

    <!-- Scripts Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#usu_id').select2({
                placeholder: 'Buscar conductor...',
                width: 'resolve'
            });
        });
    </script>

</body>
</html>
